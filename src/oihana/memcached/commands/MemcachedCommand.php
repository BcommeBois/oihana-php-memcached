<?php


namespace oihana\memcached\commands;

use Memcached;
use oihana\memcached\options\MemcachedOption;
use Throwable;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\commands\enums\ExitCode;
use oihana\commands\Kernel;
use oihana\commands\options\CommandOption;

use oihana\enums\Char;

use oihana\memcached\traits\MemcachedTrait;

use org\schema\creativeWork\Dataset;
use org\schema\PropertyValue;

use org\unece\uncefact\MeasureCode;
use org\unece\uncefact\MeasureSymbol;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function oihana\commands\helpers\clearConsole;

/**
 * Memcached command to manage the Memcached instance: display statistics, flush the cache, etc.
 *
 * @example Usage: default statistics per server
 * ```php
 * bin/console command:memcached
 * ```
 *
 * @example Verbose statistics (max size, items, connections, gets/sets)
 * ```php
 * bin/console command:memcached -v
 * bin/console command:memcached --verbose
 * ```
 *
 * @example Flush the Memcached cache
 * ```php
 * bin/console command:memcached --flush
 * ```
 *
 * @example Clear the console before running, then display stats
 * ```php
 * bin/console command:memcached --clear
 * ```
 *
 * Notes:
 * - The command name is "command:memcached" as defined in the DI definitions.
 * - The --verbose option adds extra metrics only in statistics mode (without --flush).
 * - The --clear option clears the console before executing the command.
 */
class MemcachedCommand extends Kernel
{
    /**
     * Creates a new MemcachedCommand.
     * @param string|null $name
     * @param Container|null $container
     * @param Memcached|null $memcached
     * @param array $init
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct
    (
        ?string    $name ,
        ?Container $container = null ,
        ?Memcached $memcached = null ,
        array      $init = []
    )
    {
        parent::__construct( $name , $container , $init );
        $this->memcached = $memcached;
    }

    use MemcachedTrait;

    /**
     * The default name of the command.
     */
    public const string NAME = 'command:memcached' ;

    /**
     * Configures the current command.
     * @return void
     */
    protected function configure() : void
    {
        CommandOption::configure( $this ) ;
        $this->addOption( MemcachedOption::FLUSH , 'f' , InputOption::VALUE_NONE , 'Flush the memcached memory.' );
    }

    /**
     * Executes the current command.
     * @return int 0 if everything went fine, or an exit code
     * @throws LogicException When this abstract method is not implemented
     * @see setCode()
     */
    protected function execute( InputInterface $input , OutputInterface $output ) : int
    {
        clearConsole( $input->getOption( CommandOption::CLEAR ) ?? $this->commandOptions?->clearable ?? false );

        [ $io , $timestamp ] = $this->startCommand( $input , $output );

        $flush = $input->getOption( MemcachedOption::FLUSH );

        if ( $flush === true )
        {
            $status = $this->flush( $input , $output );
        }

        else {
            $status = $this->stats( $input , $output );
        }

        $io->newLine();

        return $this->endCommand( $input , $output , $status , $timestamp );
    }

    /**
     * Flush the cache of the current Memcached instance.
     *
     * @example Flush the Memcached cache
     * ```php
     * bin/console command:memcached --flush
     * ```
     *
     * @package oihana\memcached\commands
     * @author  Marc Alcaraz (ekameleon)
     * @since   1.0.0
     */
    public function flush( InputInterface $input , OutputInterface $output ) : int
    {
        $io = $this->getIO( $input , $output );

        $io->section( 'Flush the cache' );

        try
        {
            $code = $this->memcachedFlush();

            if ( $code == Memcached::RES_SUCCESS )
            {
                $io->success( "[✓] Flush operation succeeded" );
            }

            return $code;
        }
        catch ( Throwable $exception )
        {
            $io->error( sprintf( '[!] The command failed,  %s' , $exception->getMessage() ) );
            return ExitCode::FAILURE;
        }
    }

    /**
     * Display statistics of the current Memcached instance.
     *
     * @example Usage: default statistics per server
     * ```php
     * bin/console command:memcached
     * ```
     *
     * @example Verbose statistics (max size, items, connections, gets/sets)
     * ```php
     * bin/console command:memcached -v
     * bin/console command:memcached --verbose
     * ```
     *
     * @example Clear the console before running, then display stats
     * ```php
     * bin/console command:memcached --clear
     * ```
     */
    public function stats( InputInterface $input , OutputInterface $output ) : int
    {
        $io = $this->getIO( $input , $output );
        try
        {
            $list = $this->memcachedStats( $input->getOption( MemcachedOption::VERBOSE ) );
            $datas = $list->itemListElement;

            /**
             * @var Dataset $dataSet
             */
            foreach ( $datas as $dataSet ) {
                $io->section( $dataSet->name ?? 'unknown' );

                $variables = $dataSet->variableMeasured ?? [];

                $table = new Table( $output );

                $headers = [ 'Name' , 'Value' ];
                $rows = [];

                /**
                 * @var PropertyValue $property
                 */
                foreach ( $variables as $property ) {
                    $name = $property->name ?? 'unknown';
                    $value = round( $property->value ?? 0 , 4 );

                    if ( $property->unitCode && $property->unitCode != MeasureCode::UNIT ) {
                        $unit = MeasureSymbol::getFromCode( $property->unitCode );
                        $value = $value . Char::SPACE . $unit;
                    }

                    $rows[] =
                    [
                        sprintf( '<fg=green>%s</>' , $name ) ,
                        sprintf( '<options=bold>%s</>' , $value ) ,
                    ];
                }

                $table->setHeaders( $headers )
                    ->setRows( $rows )// ->setStyle('markdown' )
                ;

                $table->render();

            }

            return ExitCode::SUCCESS;
        }
        catch ( Throwable $exception )
        {
            $io->error( sprintf( '[!] The command failed,  %s' , $exception->getMessage() ) );
            return ExitCode::FAILURE;
        }
    }
}
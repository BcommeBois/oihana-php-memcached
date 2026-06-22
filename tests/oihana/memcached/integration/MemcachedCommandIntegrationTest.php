<?php

namespace tests\oihana\memcached\integration ;

use Memcached ;
use RuntimeException ;

use DI\Container ;

use oihana\memcached\commands\MemcachedCommand ;

use Symfony\Component\Console\Application ;
use Symfony\Component\Console\Output\OutputInterface ;
use Symfony\Component\Console\Tester\CommandTester ;

use PHPUnit\Framework\Attributes\CoversClass ;
use PHPUnit\Framework\Attributes\Group ;

use function oihana\init\initContainer ;
use function oihana\init\initDefinitions ;

/**
 * A {@see Memcached} subclass whose operations always throw — used to drive the
 * defensive `catch` branches of {@see MemcachedCommand} without a broken server.
 */
class FailingMemcached extends Memcached
{
    public function flush( int $delay = 0 ) : bool
    {
        throw new RuntimeException( 'flush failed' ) ;
    }

    public function getStats( ?string $type = null ) : array|false
    {
        throw new RuntimeException( 'stats failed' ) ;
    }
}

/**
 * Live coverage of {@see MemcachedCommand}, driven through the real DI container
 * (booted exactly like `bin/console` via initContainer/initDefinitions) and a
 * Symfony {@see CommandTester}, against a running Memcached server.
 */
#[Group( 'integration' )]
#[CoversClass( MemcachedCommand::class )]
class MemcachedCommandIntegrationTest extends IntegrationTestCase
{
    private static ?Container $container = null ;

    /**
     * Boots the DI container once, mirroring `bin/console`.
     */
    private function container() : Container
    {
        if ( self::$container === null )
        {
            if ( ! defined( '__APP__' ) )         { define( '__APP__'         , dirname( __DIR__ , 4 ) . DIRECTORY_SEPARATOR ) ; }
            if ( ! defined( '__CONFIG__' ) )      { define( '__CONFIG__'      , __APP__ . 'config' ) ; }
            if ( ! defined( '__DEFINITIONS__' ) ) { define( '__DEFINITIONS__' , __APP__ . 'definitions' ) ; }

            self::$container = initContainer( initDefinitions( __DEFINITIONS__ ) ) ;
        }
        return self::$container ;
    }

    private function tester() : CommandTester
    {
        $command = $this->container()->get( Application::class )->find( 'command:memcached' ) ;
        return new CommandTester( $command ) ;
    }

    /**
     * Builds a fresh command instance bound to the given client (used to inject a
     * failing transport without touching the container-wired command).
     */
    private function testerWith( Memcached $memcached ) : CommandTester
    {
        $command = new MemcachedCommand( MemcachedCommand::NAME , $this->container() , $memcached ) ;

        // Register it on a fresh application so the global options (e.g. --verbose) exist.
        $application = new Application() ;
        $application->addCommand( $command ) ;

        return new CommandTester( $application->find( MemcachedCommand::NAME ) ) ;
    }

    public function testStatsIsTheDefaultActionAndSucceeds() : void
    {
        $this->assertSame( 0 , $this->tester()->execute( [] ) ) ;
    }

    public function testVerboseStatsSucceeds() : void
    {
        $status = $this->tester()->execute( [] , [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ] ) ;
        $this->assertSame( 0 , $status ) ;
    }

    public function testFlushClearsTheCache() : void
    {
        $cache = self::$memcached ;
        $cache->set( 'will:be:flushed' , 1 , 60 ) ;

        $this->assertSame( 0 , $this->tester()->execute( [ '--flush' => true ] ) ) ;
        $this->assertFalse( $cache->get( 'will:be:flushed' ) ) ;
    }

    public function testStatsReportsAFailureWhenTheServerThrows() : void
    {
        $tester = $this->testerWith( new FailingMemcached() ) ;
        $tester->execute( [] ) ;

        $this->assertNotSame( 0 , $tester->getStatusCode() ) ;
        $this->assertStringContainsString( 'stats failed' , $tester->getDisplay() ) ;
    }

    public function testFlushReportsAFailureWhenTheServerThrows() : void
    {
        $tester = $this->testerWith( new FailingMemcached() ) ;
        $tester->execute( [ '--flush' => true ] ) ;

        $this->assertNotSame( 0 , $tester->getStatusCode() ) ;
        $this->assertStringContainsString( 'failed' , $tester->getDisplay() ) ;
    }

    public static function tearDownAfterClass() :void
    {
        self::$container = null ;
        parent::tearDownAfterClass() ;
    }
}

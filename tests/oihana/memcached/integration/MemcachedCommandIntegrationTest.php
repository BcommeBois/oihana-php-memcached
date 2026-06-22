<?php

namespace tests\oihana\memcached\integration ;

use oihana\memcached\commands\MemcachedCommand ;

use Symfony\Component\Console\Application ;
use Symfony\Component\Console\Output\OutputInterface ;
use Symfony\Component\Console\Tester\CommandTester ;

use PHPUnit\Framework\Attributes\CoversClass ;
use PHPUnit\Framework\Attributes\Group ;

use function oihana\init\initContainer ;
use function oihana\init\initDefinitions ;

/**
 * Live coverage of {@see MemcachedCommand}, driven through the real DI container
 * (booted exactly like `bin/console` via initContainer/initDefinitions) and a
 * Symfony {@see CommandTester}, against a running Memcached server.
 */
#[Group( 'integration' )]
#[CoversClass( MemcachedCommand::class )]
class MemcachedCommandIntegrationTest extends IntegrationTestCase
{
    private static ?Application $application = null ;

    /**
     * Boots the console application once, mirroring `bin/console`.
     */
    private function application() : Application
    {
        if ( self::$application === null )
        {
            if ( ! defined( '__APP__' ) )         { define( '__APP__'         , dirname( __DIR__ , 4 ) . DIRECTORY_SEPARATOR ) ; }
            if ( ! defined( '__CONFIG__' ) )      { define( '__CONFIG__'      , __APP__ . 'config' ) ; }
            if ( ! defined( '__DEFINITIONS__' ) ) { define( '__DEFINITIONS__' , __APP__ . 'definitions' ) ; }

            self::$application = initContainer( initDefinitions( __DEFINITIONS__ ) )->get( Application::class ) ;
        }
        return self::$application ;
    }

    private function tester() : CommandTester
    {
        return new CommandTester( $this->application()->find( 'command:memcached' ) ) ;
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

    public static function tearDownAfterClass() :void
    {
        self::$application = null ;
        parent::tearDownAfterClass() ;
    }
}

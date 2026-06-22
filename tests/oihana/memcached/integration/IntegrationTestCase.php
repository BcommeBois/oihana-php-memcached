<?php

namespace tests\oihana\memcached\integration ;

use Memcached ;
use Throwable ;

use PHPUnit\Framework\TestCase ;

use oihana\memcached\enums\MemcachedConfig ;

use function oihana\init\initConfig ;

/**
 * Base class for the live Memcached integration suite (group `integration`).
 *
 * These tests are **excluded from the default `phpunit.xml` run** and executed
 * only via the dedicated config:
 *
 * ```
 * vendor/bin/phpunit -c phpunit-integration.xml
 * ```
 *
 * The server host/port are read from the `[memcached]` section of
 * `config/config.toml` (the same file the bundled `command:memcached` console
 * uses), falling back to `config.example.toml` and finally to `localhost:11211`.
 * When the server is unreachable, every test is **skipped** rather than failed —
 * the integration config sets `failOnSkipped="false"`, so a developer without a
 * local Memcached still gets a green default suite.
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * The shared client, or null when the server is unreachable.
     */
    protected static ?Memcached $memcached = null ;

    /**
     * Reason captured when the server is unavailable, surfaced by setUp().
     */
    protected static ?string $unavailable = null ;

    public static function setUpBeforeClass() :void
    {
        [ $host , $port ] = self::resolveServer() ;

        try
        {
            $memcached = new Memcached() ;
            $memcached->addServer( $host , $port ) ;

            $probe = '__oihana_probe__' . getmypid() ;
            if ( $memcached->set( $probe , 1 , 2 ) !== true || $memcached->get( $probe ) !== 1 )
            {
                self::$unavailable = "Memcached not reachable at {$host}:{$port}" ;
                return ;
            }
            $memcached->delete( $probe ) ;

            self::$memcached = $memcached ;
        }
        catch ( Throwable $exception )
        {
            self::$unavailable = 'Memcached setup failed: ' . $exception->getMessage() ;
        }
    }

    public static function tearDownAfterClass() :void
    {
        if ( self::$memcached !== null )
        {
            try { self::$memcached->flush() ; }
            catch ( Throwable ) { /* best effort */ }
        }
        self::$memcached   = null ;
        self::$unavailable = null ;
    }

    protected function setUp() :void
    {
        if ( self::$memcached === null )
        {
            $this->markTestSkipped( self::$unavailable ?? 'Memcached unavailable' ) ;
        }
        self::$memcached->flush() ; // clean slate for every test
    }

    /**
     * Resolves the server host/port from the bundled TOML config, with safe fallbacks.
     *
     * @return array{0:string,1:int}
     */
    private static function resolveServer() :array
    {
        $host = 'localhost' ;
        $port = 11211 ;

        try
        {
            $base = dirname( __DIR__ , 4 ) . DIRECTORY_SEPARATOR . 'config' ;
            $file = is_file( $base . DIRECTORY_SEPARATOR . 'config.toml' ) ? 'config.toml' : 'config.example.toml' ;

            $config    = initConfig( basePath : $base , file : $file ) ;
            $memcached = $config[ MemcachedConfig::MEMCACHED ] ?? [] ;

            $host = $memcached[ MemcachedConfig::HOST ] ?? $host ;
            $port = (int) ( $memcached[ MemcachedConfig::PORT ] ?? $port ) ;
        }
        catch ( Throwable ) { /* fall back to localhost:11211 */ }

        return [ $host , $port ] ;
    }
}

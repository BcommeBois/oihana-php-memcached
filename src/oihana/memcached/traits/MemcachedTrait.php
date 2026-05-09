<?php

namespace oihana\memcached\traits;

use Memcached;

use ReflectionException;
use UnexpectedValueException;

use oihana\memcached\enums\MemcachedStats;

use org\schema\constants\Prop;
use org\schema\creativeWork\Dataset;
use org\schema\ItemList;

use function oihana\core\maths\roundValue;

/**
 * Full-featured Memcached helper trait.
 *
 * Provides a complete API around a {@see Memcached} client: CRUD operations
 * ({@see self::memcachedGet()}, {@see self::memcachedSet()},
 * {@see self::memcachedDelete()}, etc.), administrative actions
 * ({@see self::memcachedFlush()}, {@see self::memcachedTouch()}), and
 * structured statistics ({@see self::memcachedStats()},
 * {@see self::memcachedHitRatio()}, {@see self::memcachedUptime()}).
 *
 * Composition:
 * - {@see MemcachedInitTrait} — exposes the {@see self::$memcached} property,
 *   the {@see self::MEMCACHED} key, {@see self::initializeMemcached()} and
 *   {@see self::assertMemCached()}.
 * - {@see MemcachedInfoTrait} — turns raw stats arrays into Schema.org
 *   `PropertyValue` objects used by {@see self::memcachedStats()}.
 *
 * Use this trait when you need the full surface. If you only need to *carry*
 * a Memcached client (and dispatch the calls yourself), prefer the lighter
 * {@see MemcachedInitTrait} on its own — never apply both at the same time.
 *
 * Every CRUD / stats method calls {@see self::assertMemCached()} first, so a
 * missing `$memcached` fails fast with a descriptive
 * {@see UnexpectedValueException}.
 *
 * @package oihana\memcached\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 *
 * @example Direct wiring
 * ```php
 * use oihana\memcached\traits\MemcachedTrait;
 * use Memcached;
 *
 * class CacheManager
 * {
 *     use MemcachedTrait;
 *
 *     public function __construct( string $host = 'localhost' , int $port = 11211 )
 *     {
 *         $client = new Memcached() ;
 *         $client->addServer( $host , $port ) ;
 *
 *         $this->initializeMemcached( [ self::MEMCACHED => $client ] , null ) ;
 *     }
 * }
 *
 * $cache = new CacheManager() ;
 *
 * $cache->memcachedSet( 'user:42' , [ 'name' => 'Alice' ] , 3600 ) ;
 * $user = $cache->memcachedGet( 'user:42' ) ;
 * ```
 *
 * @example Wiring from a PSR-11 container
 * ```php
 * use DI\Container;
 * use oihana\memcached\traits\MemcachedTrait;
 *
 * class CacheManager
 * {
 *     use MemcachedTrait;
 *
 *     public function __construct( Container $container , array $init = [] )
 *     {
 *         $this->initializeMemcached( $init , $container ) ;
 *     }
 * }
 *
 * // The 'cache.memcached' service id is resolved from the container:
 * $cache = new CacheManager( $container , [ CacheManager::MEMCACHED => 'cache.memcached' ] ) ;
 *
 * // Inspect basic cache stats:
 * foreach ( $cache->memcachedStats()->itemListElement as $dataset )
 * {
 *     echo $dataset->{Prop::NAME} . PHP_EOL ;
 * }
 * ```
 */
trait MemcachedTrait
{
    use MemcachedInfoTrait ,
        MemcachedInitTrait ;

    /**
     * Flush the entire memcached cache.
     *
     * @return int Returns the Memcached result code after flush operation.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager = new CacheManager();
     * $result = $cacheManager->memcachedFlush();
     * echo "Flush operation result code: $result";
     * ```
     */
    public function memcachedFlush() : int
    {
        $this->assertMemCached();
        $this->memcached->flush() ;
        return $this->memcached->getResultCode() ;
    }


    /**
     * Decrement a numeric cache value.
     *
     * @param string $key    The cache key.
     * @param int    $offset Decrement amount.
     *
     * @return int|false The new value, or false on failure.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager->memcachedDecrement('counter', 1);
     * ```
     */
    public function memcachedDecrement(string $key, int $offset = 1): int|false
    {
        $this->assertMemCached();
        return $this->memcached->decrement($key, $offset);
    }

    /**
     * Delete a key from cache.
     *
     * @param string $key The cache key.
     *
     * @return bool True on success, false on failure.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager->memcachedDelete( 'user_123' ) ;
     * ```
     */
    public function memcachedDelete(string $key): bool
    {
        $this->assertMemCached();
        return $this->memcached->delete( $key ) ;
    }

    /**
     * Get a value from cache.
     *
     * @param string $key The cache key.
     *
     * @return mixed The cached value, or false if not found.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $value = $cacheManager->memcachedGet('user_123');
     * ```
     */
    public function memcachedGet( string $key ): mixed
    {
        $this->assertMemCached();
        return $this->memcached->get( $key );
    }

    /**
     * Get all cache keys (if supported by server).
     *
     * @return array List of keys.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $keys = $cacheManager->memcachedGetAllKeys();
     * ```
     */
    public function memcachedGetAllKeys(): array
    {
        $this->assertMemCached();
        return $this->memcached->getAllKeys() ?: [];
    }

    /**
     * Calculate the cache hit ratio in percentage.
     *
     * @return float Cache hit ratio.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $ratio = $cacheManager->memcachedHitRatio();
     * ```
     */
    public function memcachedHitRatio(): float
    {
        $this->assertMemCached();
        $stats = $this->memcached->getStats();
        foreach ( $stats as $server )
        {
            $hits   = $server[ MemcachedStats::GET_HITS   ] ?? 0 ;
            $misses = $server[ MemcachedStats::GET_MISSES ] ?? 0 ;
            $total  = $hits + $misses;
            return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
        }
        return 0.0;
    }

    /**
     * Increment a numeric cache value.
     *
     * @param string $key    The cache key.
     * @param int    $offset Increment amount.
     *
     * @return int|false The new value, or false on failure.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager->memcachedIncrement('counter', 2);
     * ```
     */
    public function memcachedIncrement( string $key , int $offset = 1 ): int|false
    {
        $this->assertMemCached();
        return $this->memcached->increment($key, $offset);
    }

    /**
     * Check if a cache key exists.
     *
     * @param string $key The cache key.
     * @return bool True if exists, false otherwise.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * if ( $cacheManager->memcachedKeyExists( 'foo' ) )
     * {
     *     echo "Exists";
     * }
     * ```
     */
    public function memcachedKeyExists(string $key): bool
    {
        $this->assertMemCached();
        $this->memcached->get($key);
        return $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    /**
     * Store a value in cache.
     *
     * @param string $key  The cache key.
     * @param mixed $value The value to store.
     * @param int   $ttl   Time-to-live in seconds.
     *
     * @return bool True on success, false on failure.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager->memcachedSet( 'user_123' , ['name' => 'John'] , 3600 ) ;
     * ```
     */
    public function memcachedSet( string $key, mixed $value, int $ttl = 0 ): bool
    {
        $this->assertMemCached();
        return $this->memcached->set( $key , $value , $ttl);
    }

    /**
     * Returns the statistics of the memcached cache.
     *
     * @param bool $verbose If true, includes detailed stats; otherwise, returns basic stats.
     *
     * @return ItemList Returns an ItemList object containing cache statistics.
     *
     * @throws ReflectionException
     *
     * @example
     * ```php
     * $cacheManager = new CacheManager();
     * $basicStats = $cacheManager->memcachedStats(false);
     * $verboseStats = $cacheManager->memcachedStats(true);
     * ```
     */
    public function memcachedStats( bool $verbose = false ) : ItemList
    {
        $this->assertMemCached();

        $list = new ItemList() ;

        $stats = $this->memcached->getStats() ;

        foreach( $stats as $key => $server )
        {
            $cacheSize    = $server[ MemcachedStats::BYTES ] / ( 1024 * 1024 );
            $maxCacheSize = roundValue( $server[ MemcachedStats::LIMIT_MAX_BYTES ] / ( 1024 * 1024 ) , 5 );
            $cacheUsed    = roundValue( $cacheSize / $maxCacheSize * 100 , 5 ) ;

            $variables = [] ;

            $variables[] = $this->currentCacheSize( $cacheSize , $maxCacheSize ) ;
            $variables[] = $this->cacheUsed( $cacheUsed ) ;

            if( $verbose )
            {
                $variables[] = $this->maxCacheSize( $maxCacheSize ) ;
                $variables[] = $this->totalItems( $server ) ;
                $variables[] = $this->currentConnections( $server ) ;
                $variables[] = $this->totalConnections( $server ) ;
                $variables[] = $this->totalGets( $server ) ;
                $variables[] = $this->totalSets( $server ) ;
            }

            $list->itemListElement[] = new Dataset
            ([
                Prop::NAME              => $key ,
                Prop::VARIABLE_MEASURED => $variables
            ]) ;
        }

        return $list ;
    }

    /**
     * Change the expiration time of an existing key.
     *
     * @param string $key The cache key.
     * @param int $ttl New TTL in seconds.
     * @return bool True on success, false on failure.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $cacheManager->memcachedTouch('session_abc', 600);
     * ```
     */
    public function memcachedTouch(string $key, int $ttl): bool
    {
        $this->assertMemCached();
        return $this->memcached->touch($key, $ttl);
    }

    /**
     * Get memcached server uptime in seconds.
     *
     * @return int Uptime in seconds.
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     *
     * @example
     * ```php
     * $uptime = $cacheManager->memcachedUptime();
     * ```
     */
    public function memcachedUptime(): int
    {
        $this->assertMemCached();
        $stats = $this->memcached->getStats();
        foreach ( $stats as $server )
        {
            return $server[ MemcachedStats::UPTIME ] ?? 0;
        }
        return 0 ;
    }
}


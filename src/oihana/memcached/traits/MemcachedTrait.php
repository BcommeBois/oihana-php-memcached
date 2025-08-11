<?php

namespace oihana\memcached\traits;

use Memcached;
use UnexpectedValueException;

use oihana\memcached\enums\MemcachedStats;

use org\schema\constants\Prop;
use org\schema\creativeWork\Dataset;
use org\schema\ItemList;
use org\schema\PropertyValue;
use org\unece\uncefact\MeasureCode;
use org\unece\uncefact\MeasureName;

use function oihana\core\maths\roundValue;

/**
 * The memcached trait helper.
 *
 * This trait provides convenient methods to interact with a Memcached client,
 * including flushing the cache and retrieving detailed cache statistics.
 *
 * @package oihana\memcached\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 *
 * @example
 * ```php
 * use oihana\memcached\traits\MemcachedTrait;
 * use Memcached;
 *
 * class CacheManager
 * {
 *     use MemcachedTrait;
 *
 *     public function __construct( string $host = 'localhost' , int $port = 11211 )
 * {
 *         $this->memcached = new Memcached();
 *         $this->memcached->addServer( $host , $port ) ;
 *     }
 * }
 *
 * $cache = new CacheManager();
 *
 * // Flush cache and check result code
 * $resultCode = $cache->memcachedFlush();
 * echo "Flush result code: " . $resultCode . PHP_EOL;
 *
 * // Get basic cache stats (non verbose)
 * $statsList = $cache->memcachedStats();
 * foreach ($statsList->itemListElement as $dataset)
 * {
 *     echo $dataset->{Prop::NAME} . PHP_EOL;
 * }
 *
 * // Get verbose cache stats
 * $verboseStatsList = $cache->memcachedStats(true);
 * foreach ($verboseStatsList->itemListElement as $dataset)
 * {
 *     echo $dataset->{Prop::NAME} . PHP_EOL;
 * }
 * ```
 */
trait MemcachedTrait
{
    /**
     * The memcached client reference.
     * @var Memcached|null
     */
    public ?Memcached $memcached = null ;

    /**
     * Assert the existence of the memcached property.
     * @return void
     * @throws UnexpectedValueException If the memcached property is not set.
     */
    protected function assertMemCached():void
    {
        if( !isset( $this->memcached ) )
        {
            throw new UnexpectedValueException( 'The memcached property is not set.' ) ;
        }
    }

    /**
     * Flush the memcached cache.
     *
     * @return int Returns the Memcached result code after flush operation.
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
        $this->memcached->flush() ;
        return $this->memcached->getResultCode() ;
    }

    /**
     * Returns the statistics of the memcached cache.
     *
     * @param bool $verbose If true, includes detailed stats; otherwise, returns basic stats.
     * @return ItemList Returns an ItemList object containing cache statistics.
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
        $list = new ItemList() ;

        $stats = $this->memcached->getStats() ;

        foreach( $stats as $key => $server )
        {
            $cacheSize    = $server[ MemcachedStats::BYTES ] / ( 1024 * 1024 );
            $maxCacheSize = roundValue( $server[ MemcachedStats::LIMIT_MAX_BYTES ] / ( 1024 * 1024 ) , 5 );
            $cacheUsed    = roundValue( $cacheSize / $maxCacheSize * 100 , 5 ) ;

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
     * Indicates the cache used information.
     *
     * @param float|int $cacheUsed Cache usage percentage.
     * @return PropertyValue PropertyValue describing cache usage.
     *
     * @example
     * ```php
     * $cacheUsage = 75.5;
     * $cacheUsedProp = $cacheManager->cacheUsed($cacheUsage);
     * echo $cacheUsedProp->{Prop::NAME} . ': ' . $cacheUsedProp->{Prop::VALUE} . '%';
     * ```
     */
    public function cacheUsed( float|int $cacheUsed ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Cache used',
            Prop::DESCRIPTION => 'Cache used in percentage',
            Prop::VALUE       => $cacheUsed ,
            Prop::UNIT_CODE   => MeasureCode::PERCENT ,
            Prop::UNIT_TEXT   => MeasureName::PERCENT ,
        ]);
    }

    /**
     * Indicates the current cache size information.
     * @param int|float $cacheSize
     * @param int|float $maxCacheSize
     * @return PropertyValue
     */
    public function currentCacheSize( int|float $cacheSize , int|float $maxCacheSize ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Current cache size' ,
            Prop::DESCRIPTION => "Current size of the cache in megabytes",
            Prop::VALUE       => $cacheSize ,
            Prop::UNIT_CODE   => MeasureCode::MEGABYTE ,
            Prop::UNIT_TEXT   => MeasureName::MEGABYTE ,
            Prop::MAX_VALUE   => $maxCacheSize
        ]);
    }

    /**
     * Indicates the number of current connections.
     * @param array $server
     * @return PropertyValue
     */
    public function currentConnections( array $server ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Current connections',
            Prop::DESCRIPTION => 'Number of current connections',
            Prop::VALUE       => $server[ MemcachedStats::CURR_CONNECTIONS ] ?? 0 ,
            Prop::UNIT_CODE   => MeasureCode::UNIT ,
            Prop::UNIT_TEXT   => MeasureName::UNIT ,
        ]);
    }

    /**
     * Indicates the maximum size of the cache in megabytes.
     * @param int|float $maxCacheSize
     * @return PropertyValue
     */
    public function maxCacheSize( int|float $maxCacheSize ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Maximum cache size' ,
            Prop::DESCRIPTION => "Maximum size of the cache in megabytes",
            Prop::VALUE       => $maxCacheSize ,
            Prop::UNIT_CODE   => MeasureCode::MEGABYTE ,
            Prop::UNIT_TEXT   => MeasureName::MEGABYTE ,
        ]);
    }

    /**
     * Indicates the total number of connections.
     * @param array $server
     * @return PropertyValue
     */
    public function totalConnections( array $server ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Total connections',
            Prop::DESCRIPTION => 'Total number of connections',
            Prop::VALUE       => $server[ MemcachedStats::TOTAL_CONNECTIONS ] ?? 0 ,
            Prop::UNIT_CODE   => MeasureCode::UNIT ,
            Prop::UNIT_TEXT   => MeasureName::UNIT ,
        ]) ;
    }

    /**
     * Indicates the total number of get operations.
     * @param array $server
     * @return PropertyValue
     */
    public function totalGets( array $server ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Get operations',
            Prop::DESCRIPTION => 'Total number of get operations',
            Prop::VALUE       => $server[ MemcachedStats::CMD_GET ] ?? 0  ,
            Prop::UNIT_CODE   => MeasureCode::UNIT ,
            Prop::UNIT_TEXT   => MeasureName::UNIT ,
        ]) ;
    }

    /**
     * Indicates the total number of items stored in the cache.
     * @param array $server
     * @return PropertyValue
     */
    public function totalItems( array $server ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Total items',
            Prop::DESCRIPTION => 'Total number of items stored in the cache',
            Prop::VALUE       => $server[ MemcachedStats::CURR_ITEMS ] ?? 0 ,
            Prop::UNIT_CODE   => MeasureCode::UNIT ,
            Prop::UNIT_TEXT   => MeasureName::UNIT ,
        ]) ;
    }

    /**
     * Indicates the total number of set operations.
     * @param array $server
     * @return PropertyValue
     */
    public function totalSets( array $server ) :PropertyValue
    {
        return new PropertyValue
        ([
            Prop::NAME        => 'Set operations',
            Prop::DESCRIPTION => 'Total number of set operations',
            Prop::VALUE       => $server[ MemcachedStats::CMD_SET ] ?? 0  ,
            Prop::UNIT_CODE   => MeasureCode::UNIT ,
            Prop::UNIT_TEXT   => MeasureName::UNIT ,
        ]) ;
    }
}


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
     */
    public function memcachedFlush() : int
    {
        $this->memcached->flush() ;
        return $this->memcached->getResultCode() ;
    }

    /**
     * Returns the statistics of the memcached cache.
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
     * @param float|int $cacheUsed
     * @return PropertyValue
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


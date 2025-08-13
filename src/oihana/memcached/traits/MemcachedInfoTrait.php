<?php

namespace oihana\memcached\traits;

use oihana\memcached\enums\MemcachedStats;

use org\schema\constants\Prop;
use org\schema\PropertyValue;

use org\unece\uncefact\MeasureCode;
use org\unece\uncefact\MeasureName;

/**
 * Provides helper methods to generate detailed Memcached statistics as Schema.org `PropertyValue` objects.
 *
 * This trait focuses on transforming raw Memcached server statistics into
 * structured, semantic data, using constants from `MemcachedStats` and measurement codes
 * from the UN/CEFACT specification. Each method returns a `PropertyValue`
 * describing a specific cache metric, such as usage percentage, memory size,
 * number of connections, or total operations performed.
 *
 * Designed to be used inside classes that already have access to Memcached server stats
 * (e.g., via `$memcached->getStats()`), this trait does **not** establish a Memcached connection itself.
 *
 * @package oihana\memcached\traits
 * @since   1.0.0
 *
 * @example
 * ```php
 * use oihana\memcached\traits\MemcachedInfoTrait;
 *
 * class CacheInspector
 * {
 *     use MemcachedInfoTrait;
 *
 *     public function displayInfo(array $serverStats): void
 *     {
 *         $cacheUsedProp = $this->cacheUsed(75.5);
 *         echo $cacheUsedProp->{Prop::NAME} . ': ' . $cacheUsedProp->{Prop::VALUE} . '%';
 *
 *         $currentSizeProp = $this->currentCacheSize(50.25, 128);
 *         echo $currentSizeProp->{Prop::DESCRIPTION} . ': ' . $currentSizeProp->{Prop::VALUE} . 'MB';
 *     }
 * }
 * ```
 */
trait MemcachedInfoTrait
{
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


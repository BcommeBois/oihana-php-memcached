<?php

namespace oihana\memcached\traits;

use ReflectionException;

use oihana\memcached\enums\MemcachedStats;

use org\schema\constants\Prop;
use org\schema\PropertyValue;

use org\unece\uncefact\MeasureCode;
use org\unece\uncefact\MeasureName;

/**
 * Builds Schema.org `PropertyValue` objects from raw Memcached server stats.
 *
 * Each method maps a single cache metric (used percentage, current/max memory
 * size, current/total connections, items, total gets/sets) to a typed
 * {@see PropertyValue}, populated with:
 * - `Prop::NAME` / `Prop::DESCRIPTION` — human-readable label and description;
 * - `Prop::VALUE` — the numeric value pulled from the stats array (or
 *   computed beforehand for {@see self::cacheUsed()} and
 *   {@see self::currentCacheSize()});
 * - `Prop::UNIT_CODE` / `Prop::UNIT_TEXT` — UN/CEFACT measurement code and
 *   label (`%`, `MB`, `unit`).
 *
 * The trait does **not** open a Memcached connection nor call `getStats()`
 * itself — callers pass either a pre-extracted scalar (cache-used %, MB
 * sizes) or a single `$server` array as returned by
 * `Memcached::getStats()` per server entry. {@see MemcachedTrait::memcachedStats()}
 * is the canonical consumer.
 *
 * Stats array keys are read via the {@see MemcachedStats} enum constants
 * (e.g. {@see MemcachedStats::CURR_CONNECTIONS}). Missing keys default to `0`.
 *
 * @package oihana\memcached\traits
 * @since   1.0.0
 *
 * @example
 * ```php
 * use oihana\memcached\traits\MemcachedInfoTrait;
 * use org\schema\constants\Prop;
 *
 * class CacheInspector
 * {
 *     use MemcachedInfoTrait;
 *
 *     public function displayInfo( array $serverStats ) : void
 *     {
 *         $cacheUsedProp = $this->cacheUsed( 75.5 ) ;
 *         echo $cacheUsedProp->{Prop::NAME} . ': ' . $cacheUsedProp->{Prop::VALUE} . '%' . PHP_EOL ;
 *
 *         $currentSizeProp = $this->currentCacheSize( 50.25 , 128 ) ;
 *         echo $currentSizeProp->{Prop::DESCRIPTION} . ': ' . $currentSizeProp->{Prop::VALUE} . 'MB' . PHP_EOL ;
 *
 *         $totalItemsProp = $this->totalItems( $serverStats ) ;
 *         echo $totalItemsProp->{Prop::NAME} . ': ' . $totalItemsProp->{Prop::VALUE} . PHP_EOL ;
 *     }
 * }
 * ```
 */
trait MemcachedInfoTrait
{
    /**
     * Builds a `PropertyValue` describing the cache usage as a percentage.
     *
     * @param float|int $cacheUsed Cache usage, expressed as a percentage (0–100).
     *
     * @return PropertyValue Named "Cache used", carrying `$cacheUsed` with unit `%`.
     *
     * @throws ReflectionException
     *
     * @example
     * ```php
     * $cacheUsedProp = $cacheManager->cacheUsed( 75.5 ) ;
     * echo $cacheUsedProp->{Prop::NAME} . ': ' . $cacheUsedProp->{Prop::VALUE} . '%' ;
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
     * Builds a `PropertyValue` describing the current cache size in megabytes.
     *
     * @param int|float $cacheSize    Current cache size, in megabytes.
     * @param int|float $maxCacheSize Maximum cache size in megabytes, exposed
     *                                via `Prop::MAX_VALUE` for context.
     *
     * @return PropertyValue Named "Current cache size", with unit `MB`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the number of currently open
     * connections to the Memcached server.
     *
     * @param array $server Single-server stats array as returned by
     *                      `Memcached::getStats()`. Read key:
     *                      {@see MemcachedStats::CURR_CONNECTIONS}
     *                      (defaults to `0` if missing).
     *
     * @return PropertyValue Named "Current connections", unit `unit`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the maximum cache size in megabytes.
     *
     * @param int|float $maxCacheSize Maximum cache size, in megabytes (typically
     *                                derived from {@see MemcachedStats::LIMIT_MAX_BYTES}).
     *
     * @return PropertyValue Named "Maximum cache size", with unit `MB`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the total number of connections
     * the server has accepted since startup.
     *
     * @param array $server Single-server stats array as returned by
     *                      `Memcached::getStats()`. Read key:
     *                      {@see MemcachedStats::TOTAL_CONNECTIONS}
     *                      (defaults to `0` if missing).
     *
     * @return PropertyValue Named "Total connections", unit `unit`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the cumulative number of `get`
     * operations issued to the server since startup.
     *
     * @param array $server Single-server stats array as returned by
     *                      `Memcached::getStats()`. Read key:
     *                      {@see MemcachedStats::CMD_GET}
     *                      (defaults to `0` if missing).
     *
     * @return PropertyValue Named "Get operations", unit `unit`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the number of items currently
     * stored in the cache.
     *
     * @param array $server Single-server stats array as returned by
     *                      `Memcached::getStats()`. Read key:
     *                      {@see MemcachedStats::CURR_ITEMS}
     *                      (defaults to `0` if missing).
     *
     * @return PropertyValue Named "Total items", unit `unit`.
     *
     * @throws ReflectionException
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
     * Builds a `PropertyValue` describing the cumulative number of `set`
     * operations issued to the server since startup.
     *
     * @param array $server Single-server stats array as returned by
     *                      `Memcached::getStats()`. Read key:
     *                      {@see MemcachedStats::CMD_SET}
     *                      (defaults to `0` if missing).
     *
     * @return PropertyValue Named "Set operations", unit `unit`.
     *
     * @throws ReflectionException
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


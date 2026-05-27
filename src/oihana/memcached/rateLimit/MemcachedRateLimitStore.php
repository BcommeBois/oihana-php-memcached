<?php

namespace oihana\memcached\rateLimit ;

use Psr\Container\ContainerExceptionInterface ;
use Psr\Container\ContainerInterface ;
use Psr\Container\NotFoundExceptionInterface ;

use oihana\memcached\traits\MemcachedInitTrait ;
use oihana\middleware\rateLimit\RateLimitStore ;

/**
 * Memcached-backed implementation of {@see RateLimitStore}.
 *
 * Drop-in production store for
 * {@see \oihana\middleware\helpers\rateLimit\enforceRateLimit()} (shipped
 * by `oihana/php-middleware`). Counters are shared across every PHP
 * worker / process / node that points at the same Memcached instance,
 * so the rate-limit decision is consistent across a multi-worker
 * deployment — unlike the in-memory store shipped by the middleware
 * package, which is process-local.
 *
 * ## Atomicity
 *
 * The store implements the contract's "atomic increment, initial value
 * `1` with TTL of `$window` seconds on first creation, TTL not extended
 * on subsequent calls" semantics via the canonical Memcached counter
 * pattern :
 *
 * 1. Try `increment( $key )` first — succeeds in the steady state, no
 *    TTL touched (Memcached's increment never refreshes the TTL).
 * 2. On `false` (key does not exist), try `add( $key , 1 , $window )` —
 *    atomic, returns `true` iff this caller actually created the cell.
 *    The TTL is anchored on this first creation.
 * 3. If `add` returns `false` (another worker won the race in between),
 *    fall back to a second `increment( $key )` — guaranteed to land on
 *    the cell the other worker just created.
 *
 * Steady-state cost is **one round-trip**. Only the very first request
 * of a window for a given key pays the 2- or 3-call init.
 *
 * This sequence stays correct under concurrent worker access without
 * requiring `OPT_BINARY_PROTOCOL` (i.e. it works on the default ASCII
 * protocol). Callers wanting a single-round-trip init can enable the
 * binary protocol on their `Memcached` client and the algorithm
 * degrades gracefully — it just keeps using the 1-call path slightly
 * more often.
 *
 * ## Memcached eviction
 *
 * Under memory pressure, Memcached may evict a counter cell before its
 * TTL expires. When that happens, the next call lands on the
 * "key absent" branch and restarts the window — which is the correct
 * behaviour : an evicted counter effectively means "no recent traffic
 * recorded", so the limiter resets rather than blocking.
 *
 * @package oihana\memcached\rateLimit
 * @author  Marc Alcaraz (ekameleon)
 *
 * @example Direct instance
 * ```php
 * use Memcached ;
 * use oihana\memcached\rateLimit\MemcachedRateLimitStore ;
 *
 * $memcached = new Memcached() ;
 * $memcached->addServer( 'localhost' , 11211 ) ;
 *
 * $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;
 *
 * $count = $store->increment( 'ratelimit:auth:203.0.113.10:1700000000' , 60 ) ;
 * ```
 *
 * @example Via a PSR-11 container
 * ```php
 * use oihana\memcached\rateLimit\MemcachedRateLimitStore ;
 *
 * $store = new MemcachedRateLimitStore
 * (
 *     [ MemcachedRateLimitStore::MEMCACHED => 'cache.memcached' ] ,
 *     $container ,
 * ) ;
 * ```
 */
final class MemcachedRateLimitStore implements RateLimitStore
{
    use MemcachedInitTrait ;

    /**
     * @param array                   $init      Initialization array. Looks up {@see self::MEMCACHED}. May carry a {@see \Memcached} instance directly or a service id string resolved from `$container`.
     * @param ContainerInterface|null $container Optional PSR-11 container used to resolve a service id passed under {@see self::MEMCACHED}.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( array $init = [] , ?ContainerInterface $container = null )
    {
        $this->initializeMemcached( $init , $container ) ;
        $this->assertMemCached() ;
    }

    /**
     * {@inheritdoc}
     */
    public function increment( string $key , int $window ) : int
    {
        // Steady-state path: counter already exists, single round-trip.
        $value = $this->memcached->increment( $key ) ;

        if ( is_int( $value ) )
        {
            return $value ;
        }

        // Counter absent (first request of the window, or evicted).
        // add() is atomic — only one caller wins the race.
        if ( $this->memcached->add( $key , 1 , $window ) === true )
        {
            return 1 ;
        }

        // Another worker just created the cell in between. Increment now.
        $value = $this->memcached->increment( $key ) ;

        return is_int( $value ) ? $value : 1 ;
    }
}

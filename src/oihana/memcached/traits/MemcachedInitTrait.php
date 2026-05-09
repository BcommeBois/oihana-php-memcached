<?php

namespace oihana\memcached\traits;

use Memcached;

use UnexpectedValueException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\memcached\helpers\getMemcached;

/**
 * Lightweight trait providing only the {@see Memcached} property and the
 * boilerplate needed to initialize and assert it.
 *
 * Use this trait when a class needs to *hold* a Memcached client (typically
 * wired from a DI container or a configuration array) but does not need the
 * full CRUD / stats surface offered by {@see MemcachedTrait}. The richer trait
 * already composes this one — never use both at the same time.
 *
 * Provided members:
 * - {@see self::MEMCACHED} — canonical array / service-id key.
 * - {@see self::$memcached} — the resolved client (nullable).
 * - {@see self::initializeMemcached()} — wires `$memcached` from an `$init`
 *   array, optionally falling through to a PSR-11 container.
 * - {@see self::assertMemCached()} — guard for methods that require a non-null
 *   client.
 *
 * @package oihana\memcached\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.5
 *
 * @example Direct instance
 * ```php
 * use oihana\memcached\traits\MemcachedInitTrait;
 *
 * class CacheGate
 * {
 *     use MemcachedInitTrait;
 *
 *     public function __construct( array $init = [] )
 *     {
 *         $this->initializeMemcached( $init , null ) ;
 *     }
 * }
 *
 * $memcached = new Memcached() ;
 * $memcached->addServer( 'localhost' , 11211 ) ;
 *
 * $gate = new CacheGate( [ CacheGate::MEMCACHED => $memcached ] ) ;
 * ```
 *
 * @example Via a PSR-11 container
 * ```php
 * use DI\Container;
 * use oihana\memcached\traits\MemcachedInitTrait;
 *
 * class CacheGate
 * {
 *     use MemcachedInitTrait;
 *
 *     public function __construct( Container $container , array $init = [] )
 *     {
 *         $this->initializeMemcached( $init , $container ) ;
 *     }
 * }
 *
 * // Resolves the 'cache.memcached' service from the container:
 * $gate = new CacheGate( $container , [ CacheGate::MEMCACHED => 'cache.memcached' ] ) ;
 * ```
 */
trait MemcachedInitTrait
{
    /**
     * Canonical key used to look up the Memcached client in the `$init` array
     * passed to {@see self::initializeMemcached()}, and the default service id
     * key when resolving from a PSR-11 container.
     */
    public const string MEMCACHED = 'memcached' ;

    /**
     * The Memcached client reference, or `null` when the trait has not been
     * initialized or no matching definition was found.
     *
     * @var Memcached|null
     */
    public ?Memcached $memcached = null ;

    /**
     * Asserts that {@see self::$memcached} has been set.
     *
     * Call this at the top of any method that dereferences `$this->memcached`,
     * to fail fast with a descriptive message instead of a generic
     * "method on null" type error.
     *
     * @return void
     *
     * @throws UnexpectedValueException If the memcached property is not set.
     */
    protected function assertMemCached() :void
    {
        if( !isset( $this->memcached ) )
        {
            throw new UnexpectedValueException( 'The memcached property is not set.' ) ;
        }
    }

    /**
     * Initializes the Memcached client dependency from the `$init` array.
     *
     * Mirrors the canonical `$init` / `$container` pattern used across the
     * Oihana stack. The value found under {@see self::MEMCACHED} in `$init`
     * may be:
     *
     * - a {@see Memcached} instance — used as-is;
     * - a non-empty `string` — treated as a service id and resolved from
     *   `$container` (when both `$container` is provided and `has()` returns
     *   `true`);
     * - anything else (including a missing key) — leaves
     *   {@see self::$memcached} as `null`.
     *
     * Because the property may stay `null`, downstream code must guard
     * accesses with `if( $this->memcached )` or call
     * {@see self::assertMemCached()} when a client is mandatory.
     *
     * Resolution itself is delegated to {@see getMemcached()}.
     *
     * @param array                   $init      The initialization array, typically
     *                                           the one passed to the constructor.
     *                                           Looks up {@see self::MEMCACHED}.
     * @param ContainerInterface|null $container Optional PSR-11 container used to
     *                                           resolve string service ids.
     *
     * @return static
     *
     * @throws ContainerExceptionInterface If the container throws while resolving the service id.
     * @throws NotFoundExceptionInterface  If the container reports the service id missing
     *                                     after `has()` returned `true`.
     *
     * @see getMemcached()
     */
    protected function initializeMemcached( array $init , ?ContainerInterface $container ) :static
    {
        $this->memcached = getMemcached( $init , $container , self::MEMCACHED ) ;
        return $this ;
    }
}


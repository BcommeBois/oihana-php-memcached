<?php

namespace oihana\memcached\helpers;

use Memcached;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Resolves a {@see Memcached} instance from various types of input definitions.
 *
 * Behavior:
 * - {@see Memcached} instance → returned as-is.
 * - array → looked up under `$key` (default `'memcached'`).
 * - string + container.has() → resolved via the container.
 * - otherwise → `$default`.
 *
 * @param array|string|Memcached|null $definition
 * @param ContainerInterface|null     $container
 * @param string                      $key
 * @param Memcached|null              $default
 *
 * @return Memcached|null
 *
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 *
 * @author  Marc Alcaraz (eKameleon)
 * @package oihana\memcached\helpers
 * @version 1.0.4
 */
function getMemcached
(
    array|string|null|Memcached $definition = null ,
    ?ContainerInterface         $container  = null ,
    string                      $key        = 'memcached' ,
    ?Memcached                  $default    = null ,
)
:?Memcached
{
    if( $definition instanceof Memcached )
    {
        return $definition ;
    }

    if( is_array( $definition ) )
    {
        $definition = $definition[ $key ] ?? null ;
    }

    if( is_string( $definition ) && !empty( $definition ) && $container?->has( $definition ) )
    {
        $definition = $container->get( $definition ) ;
    }

    return $definition instanceof Memcached ? $definition : $default ;
}
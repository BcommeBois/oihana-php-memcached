<?php

use DI\Container;

use oihana\memcached\controllers\MemcachedController;
use oihana\memcached\enums\MemcachedAction;
use oihana\memcached\enums\MemcachedDefinition;
use oihana\routes\http\GetRoute;
use oihana\routes\Route;

/**
 * Dependency Injection definitions for integrating Memcached actions
 * into a Slim PHP routing process.
 *
 * This configuration provides:
 *
 * 1. A Memcached controller bound to a DI container entry
 *    (`api:controller:memcached`).
 *    - The controller is responsible for handling Memcached operations.
 *    - It is automatically constructed with the container and the Memcached
 *      service definition.
 *
 * 2. A route definition for flushing the cache:
 *    - Endpoint: `GET /memcached/flush`
 *    - Controller: {@see MemcachedController}
 *    - Action: {@see MemcachedAction::FLUSH}
 *
 * 3. A route definition for retrieving Memcached statistics:
 *    - Endpoint: `GET /memcached/stats`
 *    - Query option: `?skin=full` to return detailed statistics
 *    - Controller: {@see MemcachedController}
 *    - Action: {@see MemcachedAction::STATS}
 *
 * Usage:
 * Place this file in the DI configuration path of your Slim application.
 * The defined services and routes will be automatically registered and
 * available to handle requests.
 *
 * @return array<string,mixed> Array of DI container definitions for
 *                             Memcached controller and routes.
 *
 * @see The 'memcached' definition in the 'definitions/cache/memcached.php' file.
 */
return
[
    'api:controller:memcached' => fn( Container $container ) => new MemcachedController
    (
        $container ,
        $container->get( MemcachedDefinition::MEMCACHED ) // see definitions/cache/memcached.php
    ) ,

    // http://example.com/memcached/flush
    'api:route:memcached:flush' => fn( Container $container ) => new GetRoute( $container ,
    [
        Route::CONTROLLER_ID => 'api:controller:memcached' ,
        Route::ROUTE         => '/memcached/flush',
        Route::METHOD        => MemcachedAction::STATS ,
    ]) ,

    // http://example.com/memcached/stats
    // http://example.com/memcached/stats?skin=full
    'api:route:memcached:stats' => fn( Container $container ) => new GetRoute( $container ,
    [
        Route::CONTROLLER_ID => 'api:controller:memcached' ,
        Route::ROUTE         => '/memcached/stats' ,
        Route::METHOD        => MemcachedAction::STATS ,
    ]) ,
];

<?php

use Psr\Container\ContainerInterface ;

use oihana\memcached\enums\MemcachedConfig      as Config      ;
use oihana\memcached\enums\MemcachedDefinitions as Definitions ;

return
[
    Definitions::COMMAND
        => fn( ContainerInterface $container ) :array
        => $container->get( Definitions::CONFIG )[ Config::COMMAND ] ?? []
];
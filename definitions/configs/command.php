<?php

use Psr\Container\ContainerInterface ;

use oihana\memcached\enums\MemcachedConfig     as Config      ;
use oihana\memcached\enums\MemcachedDefinition as Definition ;

return
[
    Definition::COMMAND
        => fn( ContainerInterface $container ) :array
        => $container->get( Definition::CONFIG )[ Config::COMMAND ] ?? []
];
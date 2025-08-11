<?php

use DI\Container;

use oihana\enums\Param;
use oihana\memcached\commands\MemcachedCommand;

use oihana\memcached\enums\MemcachedDefinitions as Definitions;

// bin/console command:memcached
// bin/console command:memcached -v
// bin/console command:memcached --verbose
// bin/console command:memcached --flush
// bin/console command:memcached --flush -v

return
[
    MemcachedCommand::NAME => fn( Container $container ) => new MemcachedCommand
    (
        MemcachedCommand::NAME ,
        $container ,
        $container->get( Definitions::MEMCACHED ) ,
        [
            Param::DESCRIPTION => 'List and flush memcached.' ,
            Param::HELP        => 'This command allows manage the memcached tool.' ,

            ...$container->get( Definitions::COMMAND )
        ]
    )
];

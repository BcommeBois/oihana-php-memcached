<?php

use DI\Container;

use oihana\commands\enums\CommandParam;
use oihana\enums\aram;
use oihana\memcached\commands\MemcachedCommand;

use oihana\memcached\enums\MemcachedDefinition as Definition;

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
        $container->get( Definition::MEMCACHED ) ,
        [
            CommandParam::DESCRIPTION => 'List and flush memcached.' ,
            CommandParam::HELP        => 'This command allows manage the memcached tool.' ,

            ...$container->get( Definition::COMMAND )
        ]
    )
];

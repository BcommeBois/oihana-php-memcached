<?php

use oihana\memcached\enums\MemcachedDefinition as Definition;

return
[
    Definition::CONFIG_PATH => fn() :string => __CONFIG__
];

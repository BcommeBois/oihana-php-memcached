<?php

use oihana\memcached\enums\MemcachedDefinitions as Definitions;

return
[
    Definitions::CONFIG_PATH => fn() :string => __CONFIG__
];

<?php

use oihana\memcached\enums\MemcachedDefinition as Definition;

return
[
    Definition::APP_PATH => fn() => __APP__
];

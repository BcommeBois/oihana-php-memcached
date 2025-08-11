<?php

use oihana\memcached\enums\MemcachedDefinitions as Definitions;

return
[
    Definitions::APP_PATH => fn() => __APP__
];

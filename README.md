# Oihana PHP - memcached

![Oihana PHP System](https://raw.githubusercontent.com/BcommeBois/oihana-php-memcached/main/assets/images/oihana-php-memcached-logo-inline-512x160.png)

PHP library for managing MemCached, built on top of the Oihana project libraries (oihana-php-commands, etc.).

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-memcached.svg?style=flat-square)](https://packagist.org/packages/oihana/php-memcached)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-memcached.svg?style=flat-square)](https://packagist.org/packages/oihana/php-memcached)  
[![License](https://img.shields.io/packagist/l/oihana/php-memcached.svg?style=flat-square)](LICENSE)

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)**

Install via [Composer](https://getcomposer.org):
```bash
composer require oihana/php-system
```

## 🚀 Quick Start

### List memcache

List the memcache information
```
bin/console command:memcached

Command:memcached
=================

localhost:11211
---------------

+--------------------+-------+
| Name               | Value |
+--------------------+-------+
| Current cache size | 0 MB  |
| Cache used         | 0 %   |
+--------------------+-------+

✅  Done in 5 ms
----------------

 Thank you and see you soon!
```

List all the memcache information
```
bin/console command:memcached -v

Command:memcached
=================

localhost:11211
---------------

+---------------------+-------+
| Name                | Value |
+---------------------+-------+
| Current cache size  | 0 MB  |
| Cache used          | 0 %   |
| Maximum cache size  | 64 MB |
| Total items         | 0     |
| Current connections | 2     |
| Total connections   | 10    |
| Get operations      | 0     |
| Set operations      | 0     |
+---------------------+-------+

✅  Done in 6 ms
----------------

 Thank you and see you soon!
```

### flush memcache

Flush the memcache 
```
bin/console command:memcached --flush

Command:memcached
=================

Flush the cache
---------------
                                                                                                                        
 [OK] [✓] Flush operation succeed                                                                                       
                                                                                                                        
✅  Done in 4 ms
----------------
```

## ✅ Running Unit Tests

To run all tests:
```bash
composer run-script test
```

## 🧾 Licence

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr
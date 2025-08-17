# Oihana PHP Memcached OpenSource library - Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
and follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- oihana\memcached\enums\MemcachedAction
- oihana\memcached\helpers\cacheCollection -> require matthiasmullie/scrapbook (composer.json)

---

## [1.0.2] - 2025-08-12

### Added

- Creates the MemcachedInfoTrait to isolate methods to generate detailed Memcached statistics.
- Adds CRUD methods in the oihana\memcached\traits\MemcachedTrait
  - memcachedDecrement
  - memcachedDelete
  - memcachedGet
  - memcachedGetAllKeys
  - memcachedHitRatio
  - memcachedIncrement
  - memcachedKeyExists
  - memcachedSet
  - memcachedTouch
  - memcachedUptime
 

## [1.0.0] - 2025-08-11

### Added

- Adds oihana\memcached\commands\MemCachedCommand
- Adds oihana\memcached\enums\MemCachedConfig
- Adds oihana\memcached\enums\MemCachedDefinitions
- Adds oihana\memcached\enums\MemcachedStats
- Adds oihana\memcached\traits\MemcachedTrait

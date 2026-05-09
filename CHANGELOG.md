# Oihana PHP Memcached OpenSource library - Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
and follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.4] - 2026-05-09

### Added

- Adds the `oihana\memcached\helpers\getMemcached` helper to resolve a `Memcached` instance from a direct instance, an init array, or a PSR-11 container service id.
- Adds the `oihana\memcached\traits\MemcachedTrait::initializeMemcached` method to wire the `Memcached` dependency from an init array / container, following the canonical Oihana `$init` / `$container` pattern.
- Adds unit tests for the `getMemcached` helper and the `MemcachedTrait::initializeMemcached` method.

### Changed

- `MemcachedTrait::initializeMemcached` now accepts a PSR-11 `ContainerInterface` instead of being tied to `DI\Container`.

### Fixed

- Fix constants in the MemcachedDefinition enumeration.
- Fix the MemcacheController class, the fail method use now the $request parameter.

---

## [1.0.3] - 2025-08-18

### Added

- oihana\memcached\controllers\MemcachedController
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

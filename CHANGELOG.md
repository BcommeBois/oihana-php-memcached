# Oihana PHP Memcached OpenSource library - Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)  
and follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- Adds the `oihana\memcached\rateLimit\MemcachedRateLimitStore` class — production-grade implementation of the `oihana\middleware\rateLimit\RateLimitStore` interface (shipped by `oihana/php-middleware` v0.3+). Backs the `enforceRateLimit()` helper with a Memcached-shared counter, so the rate-limit decision is consistent across every PHP worker / process / node that points at the same Memcached instance. Composes `MemcachedInitTrait` for canonical `$init` / `$container` wiring. Uses the canonical Memcached counter pattern (`increment` first, fall back to `add` then re-`increment` on race) — atomic on the default ASCII protocol, single round-trip in the steady state, anchors TTL on the first request without ever extending it on subsequent increments. Resilient to Memcached eviction (an evicted counter restarts the window, which is the correct behaviour).
- Adds 10 PHPUnit tests covering the constructor (direct instance, missing-instance guard, container resolution) and the `increment()` contract (steady-state, init with TTL, add-race fallback, defensive fallback when the second increment also fails, TTL never extended on subsequent calls, key segregation).

### Changed

- Adds `oihana/php-middleware: dev-main` to `require` so the package can implement the `RateLimitStore` interface directly. Bumps the package description to mention the new rate-limit store and adds `rate-limit`, `rate-limiting`, `psr-7`, `middleware` to the keywords.

---

## [1.0.5] - 2026-05-09

### Added

- Adds the lightweight `oihana\memcached\traits\MemcachedInitTrait` for classes that only need to hold and initialize a `Memcached` client without the full CRUD / stats surface.
- Adds a unit test covering the fluent return of `MemcachedInitTrait::initializeMemcached()`.

### Changed

- Extracts the `MEMCACHED` const, the `$memcached` property, `initializeMemcached()` and `assertMemCached()` from `MemcachedTrait` into the new `MemcachedInitTrait`. `MemcachedTrait` now composes `MemcachedInitTrait` and `MemcachedInfoTrait` — public API unchanged.
- `MemcachedInitTrait::initializeMemcached()` now returns `static` to allow fluent chaining (`return $this`).
- Enriches the PHPDoc of `MemcachedTrait`, `MemcachedInfoTrait` and `MemcachedInitTrait`: composition notes, canonical `$init` / `$container` examples, and per-method documentation of the `MemcachedStats` keys consumed and units returned.

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

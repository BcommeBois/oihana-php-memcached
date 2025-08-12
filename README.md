# Oihana PHP - memcached

![Oihana PHP System](https://raw.githubusercontent.com/BcommeBois/oihana-php-memcached/main/assets/images/oihana-php-memcached-logo-inline-512x160.png)

This plugin provides CLI commands to control **Memcached** directly, using the PHP [Memcached extension](https://www.php.net/manual/en/book.memcached.php).

Built on top of the [Oihana PHP Commands](https://github.com/BcommeBois/oihana-php-commands/) Library.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-memcached.svg?style=flat-square)](https://packagist.org/packages/oihana/php-memcached)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-memcached.svg?style=flat-square)](https://packagist.org/packages/oihana/php-memcached)  
[![License](https://img.shields.io/packagist/l/oihana/php-memcached.svg?style=flat-square)](LICENSE)

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)**

Install via [Composer](https://getcomposer.org):

### 
```shell
composer require oihana/php-memcached
```

## 🚀 Quick Start

Memcached is an in-memory key–value store used to speed up dynamic web applications by caching data in RAM, reducing database load and latency. 
In PHP, you can use it through the [Memcached extension](https://www.php.net/manual/en/book.memcached.php).

Memcached is best for storing small, frequently accessed data like session data, API responses, or precomputed query results.

⚠️ It’s not persistent — data is lost if the server restarts.

### Initial setup

To run the memcached command locally, you need to create the configuration file `config/config.toml`. 

You can do this by copying and editing the example config:
```shell
cp config/config.example.toml config/config.toml
```

### List Memcached info

Display basic Memcached information:
```shell
bin/console command:memcached
```

Example output:
```shell
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

### List detailed Memcached info

Display full Memcached statistics:
```shell
bin/console command:memcached -v
```

Example output:
```shell
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

### Flush Memcached cache

Clear the entire Memcached cache:
```shell
bin/console command:memcached --flush
```

Example output:
```shell
Command:memcached
=================

Flush the cache
---------------
                                                                                                                        
 [OK] [✓] Flush operation succeeded                                                                                       
                                                                                                                        
✅  Done in 4 ms
----------------
```

### Use composer

You can run a composer script:  
```shell
composer memcache
composer memcache -- -v
composer memcache -- --verbose

composer memcache -- -f
composer memcache -- --flush 
```

## ✅ Running Unit Tests

To run all tests:
```shell
composer test
```

## 🧾 License

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr

## ⚙️ Installing Memcached

This section provides a step-by-step guide to installing and configuring Memcached.

### Linux (Debian)

#### 1. Update packages (recommended)

Before installing, make sure your package lists are up to date:
```shell
sudo apt update
sudo apt upgrade
```

#### 2. Install the Memcached server

This installs the Memcached daemon, which handles in-memory caching:
```shell
$ sudo apt update
$ sudo apt install memcached
$ sudo apt install libmemcached-tools
```

Package descriptions:
- memcached — The Memcached server daemon. 
- libmemcached-tools — Command-line utilities to interact with Memcached (e.g., for viewing statistics).

#### 3. Install memcflush (optional but useful)

```shell
curl -O https://raw.githubusercontent.com/memcached/memcached/master/scripts/memcflush.pl
chmod +x memcflush.pl
sudo mv memcflush.pl /usr/local/bin/memcflush
```

#### 4. Verify Perl installation
```shell
$ perl --version
```

#### 5. Start Memcached if necessary
```shell
$ sudo systemctl start memcached
```

#### 6. Configure the Memcached server (optional but recommended)

The main configuration file is:
```shell
sudo pico /etc/memcached.conf
```

Common parameters:
- -m 64 (ou -m 1024 par exemple) : Memory allocation in MB for Memcached (default is often 64 MB).
- -p 11211 :  TCP port to listen on (default: 11211).
- -l 127.0.0.1 : Listening IP address. For security, keep this as 127.0.0.1 unless remote access is required (and firewall rules are properly set).
- -U 0 : Disables the UDP protocol to mitigate certain attacks.

After modifying the configuration file, restart Memcached:
```shell
sudo systemctl restart memcached
sudo systemctl enable memcached # Start automatically at boot
```

#### 7. Install the PHP Memcached extension
```shell
sudo apt install php8.4-memcached
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
```

If you have multiple PHP versions, ensure you install the extension for the version your application uses (e.g., php8.4-memcached for PHP 8.4).

Verify installation:
```php
<?php
phpinfo();
?>
```

Open it in a browser and search for “memcached” — you should see a dedicated section showing it’s enabled.

You can also check via CLI:
```shell
php -m | grep memcached
```

#### 8. Verify Memcached is running

```shell
echo "stats settings" | nc localhost 11211
```

#### 9. Secure Memcached

##### Limit access to localhost

By default, Memcached listens on 127.0.0.1 (localhost). Verify this setting:

```shell
sudo pico /etc/memcached.conf
```

Check for: 
```shell
-l 127.0.0.1
```
This prevents external connections.

⚠️ Never expose Memcached directly to the internet.

### MacOS

#### 1. Install Memcached and tools

Make sure you have [Homebrew](https://brew.sh/) installed first.

```shell
brew install memcached
brew install libmemcached
```

#### 2. Install memcflush

```shell
curl -O https://raw.githubusercontent.com/memcached/memcached/master/scripts/memcflush.pl
chmod +x memcflush.pl
sudo mv memcflush.pl /usr/local/bin/memcflush
```

#### 3. Verify Perl installation

```shell
perl --version
```

#### 4. Test memflush

```shell
$ memflush --servers=localhost:11211
```
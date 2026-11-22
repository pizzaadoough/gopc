**GOPC - Test Environment**

**1. Environment**

- Operating system: Ubuntu 24.04 LTS
- Host processor: Intel Xeon E5530
- Virtual CPUs: 4 vCPUs
- Memory: 8 GB RAM
- PHP runtime: PHP 8.3.6
- Web server: Nginx
- PHP interfaces: CLI and FPM

Install the following packages from the Ubuntu repositories:

```bash
sudo apt update
sudo apt install nginx php8.3-cli php8.3-fpm php8.3-opcache php8.3-common
```

Verify that the installed PHP runtime and loader have compatible versions, ABIs, architectures, and thread-safety configurations. The standard Ubuntu PHP packages use NTS builds. The installed PHP patch version may differ from the tested version due to package updates.

**2. CLI Test**

From the directory containing `test-exec.php`, run:

```bash
php -n -d "extension=/absolute/path/to/gc_loader.so" test-exec.php
```

Replace `/absolute/path/to/` with the actual loader directory. The `-n` option runs PHP without loading its default configuration files.

**3. PHP-FPM Test**

Place `gc_loader.so` in the PHP extension directory, or specify its absolute path in the configuration below.

Create `/etc/php/8.3/mods-available/gopc.ini` with the following content:

```ini
extension=gc_loader.so
```

If the loader is outside the PHP extension directory, specify its absolute path:

```ini
extension=/absolute/path/to/gc_loader.so
```

Enable the loader for PHP-FPM only and restart the service:

```bash
sudo phpenmod -v 8.3 -s fpm gopc
sudo systemctl restart php8.3-fpm
```

The `phpenmod` command creates the required symbolic link in `/etc/php/8.3/fpm/conf.d/`.

Configure Nginx to serve PHP requests through PHP-FPM. Place `test-exec.php` in the configured document root and access it through the web server in a controlled test environment.

**4. Benchmark Results (PHP-FPM)**

```text
Cold execution : 2.350 ms
Warm average   : 0.782 ms
Warm median    : 0.778 ms
Warm P95       : 0.818 ms
Warm min       : 0.752 ms
Warm max       : 0.888 ms
```

**Cache statistics before execution:**

```text
Array
(
    [enabled] => 1
    [hits] => 0
    [misses] => 0
    [stores] => 0
)
```

**Cache statistics after execution:**

```text
Array
(
    [enabled] => 1
    [hits] => 199
    [misses] => 1
    [stores] => 1
)
```

These results reflect the test environment and are provided for reference only. Actual performance may vary depending on hardware, PHP settings, system load, and workload.

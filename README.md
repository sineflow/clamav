This library is a PHP client for working with a ClamAV daemon. It also provides optional Symfony integration.

[![Workflow status](https://github.com/sineflow/clamav/actions/workflows/code_style_and_tests.yml/badge.svg)](https://github.com/sineflow/clamav/actions/workflows/code_style_and_tests.yml)
[![Coverage Status](https://coveralls.io/repos/github/sineflow/clamav/badge.svg?branch=main)](https://coveralls.io/github/sineflow/clamav?branch=main)

# Requirements:

You need to have ClamAV installed and configured to accept socket and/or network connections: https://docs.clamav.net/manual/Installing.html

# Installation
```sh
$ composer require sineflow/clamav
```

# Usage as a standalone library
```php
$scanner = new Scanner(new ScanStrategyClamdUnix($socket));
$scanner = new Scanner(new ScanStrategyClamdNetwork($host, $port));
```

# Usage as a Symfony bundle
## Enable the bundle
```php
// config/bundles.php

return [
    // ...
    Sineflow\ClamAV\Bundle\SineflowClamAVBundle::class => ['all' => true],
];
```

## Configuration:
```yaml
sineflow_clam_av:
    strategy: clamd_unix
    socket: "/var/run/clamav/clamd.ctl"
```
or
```yaml
sineflow_clam_av:
    strategy: clamd_network
    host: 127.0.0.1
    port: 3310
```

## Scanning files
```php
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Exception\SocketException;

public function myAction(Scanner $scanner)
{
    try {
        $scannedFile = $scanner->scan($file);
        if (!$scannedFile->isClean()) {
            echo $scannedFile->getVirusName();
        }
    } catch (SocketException $e) {
        ...
    } catch (FileScanException $e) {
        ...
    }
}
```

## Scanning streams

When the file is not on the local filesystem or not accessible to the ClamAV daemon (e.g. files stored via Flysystem in S3, SFTP, etc.), you can use `scanStream()` to send the file contents directly to ClamAV via its INSTREAM protocol.

```php
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Exception\SocketException;

// In a real application, the stream would typically come from
// Flysystem's readStream(), an HTTP response, or similar source.
public function myAction(Scanner $scanner)
{
    $stream = fopen('/path/to/file', 'rb');
    try {
        $scannedFile = $scanner->scanStream($stream, 'my-upload.pdf');
        if (!$scannedFile->isClean()) {
            echo $scannedFile->getVirusName();
        }
    } catch (SocketException $e) {
        ...
    } catch (FileScanException $e) {
        ...
    } finally {
        fclose($stream);
    }
}
```

## Running tests
```
docker compose run --rm phpunit
docker compose down
```

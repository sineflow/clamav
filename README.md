This library is a PHP client for working with a ClamAV daemon. It also provides optional Symfony integration.

[![Build Status](https://app.travis-ci.com/sineflow/clamav.svg?branch=master)](https://app.travis-ci.com/github/sineflow/clamav)

# Requirements:

You need to have ClamAV installed and configured to accept socket and/or network connections: https://docs.clamav.net/manual/Installing.html

# Installation
```
$ composer require sineflow/clamav
```

# Usage as a standalone library
```
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
```
sineflow_clam_av:
    strategy: clamd_unix
    socket: "/var/run/clamav/clamd.ctl"
```
or
```
sineflow_clam_av:
    strategy: clamd_network
    host: 127.0.0.1
    port: 3310
```

## Scanning files
```
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

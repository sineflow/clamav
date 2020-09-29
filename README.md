This library is a PHP client for working with a ClamAV daemon. It also provides optional Symfony integration.

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
## Configuration:
```
sineflow_clamav:
    strategy: clamd_unix
    socket: "/var/run/clamav/clamd.ctl"
```
or
```
sineflow_clamav:
    strategy: clamd_network
    host: 127.0.0.1
    port: 3310
```

## Scanning files
```
public function myAction(Scanner $scanner)
{
    $scannedFile = $scanner->scan($file);
    if (!$scannedFile->isClean()) {
        echo $scannedFile->getVirusName();
    }
}
```

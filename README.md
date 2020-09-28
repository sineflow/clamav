# Configuration:
```
sineflow_clamav:
    strategy: clamd_unix | clamd_network
    socket: "/var/run/clamav/clamd.ctl"
    host: 127.0.0.1
    port: 3310
```

# Usage:
```
$scanner = new Scanner(new ScanStrategyClamdUnix($socket));
$scanner = new Scanner(new ScanStrategyClamdNetwork($host, $port));

```

or in Symfony:

```
public function myAction(Scanner $scanner)
{
    $scannedFile = $scanner->scan($file);
    if (!$scannedFile->isClean()) {
        echo $scannedFile->getVirusName();
    }
}
```

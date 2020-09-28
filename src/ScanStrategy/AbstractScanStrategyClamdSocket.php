<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Socket\Socket;

abstract class AbstractScanStrategyClamdSocket
{
    /**
     * @var Socket
     */
    protected $socket;

    public function scan(string $filePath): ScannedFile
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException(sprintf('%s is not a file', $filePath));
        }

        $response = $this->socket->sendCommand('SCAN ' . $filePath);

        return new ScannedFile($response);
    }

    public function version()
    {
        // TODO: Implement version() method.
    }

    public function ping()
    {
        // TODO: Implement ping() method.
    }

}

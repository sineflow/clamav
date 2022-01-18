<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Socket\Socket;

abstract class AbstractScanStrategyClamdSocket
{
    /**
     * @var Socket
     */
    protected $socket;

    /**
     * @param string $filePath
     *
     * @return ScannedFile
     */
    public function scan(string $filePath): ScannedFile
    {
        // clamav can scan a directory, but we don't support this at the moment
        if (!is_file($filePath)) {
            throw new FileScanException($filePath, 'Not a file.');
        }

        $response = $this->socket->sendCommand('SCAN ' . $filePath);

        return new ScannedFile($response);
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return trim($this->socket->sendCommand('VERSION'));
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return trim($this->socket->sendCommand('PING')) === 'PONG';
    }
}

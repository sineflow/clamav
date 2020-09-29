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

    /**
     * @param string $filePath
     *
     * @return ScannedFile
     */
    public function scan(string $filePath): ScannedFile
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException(sprintf('%s is not a file', $filePath));
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

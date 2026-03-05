<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Exception\SocketException;
use Sineflow\ClamAV\Socket\Socket;

abstract class AbstractScanStrategyClamdSocket implements ScanStrategyInterface
{
    protected Socket $socket;

    /**
     * @throws FileScanException
     * @throws SocketException
     */
    public function scan(string $filePath): ScannedFile
    {
        // clamav can scan a directory, but we don't support this at the moment
        if (!is_file($filePath)) {
            throw new FileScanException($filePath, 'Not a file.');
        }

        $response = $this->socket->sendCommand('SCAN ' . $filePath);

        return ScannedFile::fromRawResponse($filePath, $response);
    }

    /**
     * @param resource $stream   Open readable stream
     * @param string   $fileName Identifier for logging/error messages
     *
     * @throws FileScanException
     * @throws SocketException
     */
    public function scanStream($stream, string $fileName): ScannedFile
    {
        $response = $this->socket->sendInstreamFromStream($stream);

        return ScannedFile::fromInstreamResponse($fileName, $response);
    }

    public function version(): string
    {
        return trim($this->socket->sendCommand('VERSION'));
    }

    public function ping(): bool
    {
        return trim($this->socket->sendCommand('PING')) === 'PONG';
    }
}

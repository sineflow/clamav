<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\Socket\Socket;

class ScanStrategyClamdNetwork extends AbstractScanStrategyClamdSocket implements ScanStrategyInterface
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '3310';

    /**
     * @param string $host
     * @param string $port
     */
    public function __construct(string $host = self::DEFAULT_HOST, string $port = self::DEFAULT_PORT)
    {
        $this->socket = new Socket(Socket::NETWORK, [$host, $port]);
    }
}

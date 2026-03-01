<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\Socket\Socket;

class ScanStrategyClamdNetwork extends AbstractScanStrategyClamdSocket
{
    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 3310;

    public function __construct(string $host = self::DEFAULT_HOST, int $port = self::DEFAULT_PORT, ?int $socketTimeout = null)
    {
        $this->socket = new Socket(Socket::NETWORK, [$host, $port], $socketTimeout);
    }
}

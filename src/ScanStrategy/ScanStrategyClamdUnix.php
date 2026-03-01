<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\Socket\Socket;

class ScanStrategyClamdUnix extends AbstractScanStrategyClamdSocket
{
    public const DEFAULT_SOCKET = '/var/run/clamav/clamd.ctl';

    public function __construct(string $socketAddress = self::DEFAULT_SOCKET, ?int $socketTimeout = null)
    {
        $this->socket = new Socket(Socket::UNIX, [$socketAddress], $socketTimeout);
    }
}

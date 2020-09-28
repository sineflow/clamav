<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\Socket\Socket;

class ScanStrategyClamdUnix extends AbstractScanStrategyClamdSocket implements ScanStrategyInterface
{
    const DEFAULT_SOCKET = '/var/run/clamav/clamd.ctl';

    /**
     * @param string $socketAddress
     */
    public function __construct(string $socketAddress = self::DEFAULT_SOCKET)
    {
        $this->socket = new Socket(Socket::UNIX, [$socketAddress]);
    }
}

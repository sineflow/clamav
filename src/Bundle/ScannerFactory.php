<?php

namespace Sineflow\ClamAV\Bundle;

use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;

/**
 * Instantiates a Scanner using a ScanStrategy based on the configuration
 */
class ScannerFactory
{
    public static function createScanner(array $options): Scanner
    {
        $socketTimeout = $options['socket_timeout'] ?? null;
        $strategy = $options['strategy'] ?? null;

        $scanStrategy = match ($strategy) {
            'clamd_unix' => new ScanStrategyClamdUnix(
                $options['socket'] ?? ScanStrategyClamdUnix::DEFAULT_SOCKET,
                $socketTimeout,
            ),
            'clamd_network' => new ScanStrategyClamdNetwork(
                $options['host'] ?? ScanStrategyClamdNetwork::DEFAULT_HOST,
                $options['port'] ?? ScanStrategyClamdNetwork::DEFAULT_PORT,
                $socketTimeout,
            ),
            default => throw new \InvalidArgumentException(
                sprintf('Unsupported scan strategy "%s" configured', $strategy)
            ),
        };

        return new Scanner($scanStrategy);
    }
}

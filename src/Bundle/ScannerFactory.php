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
    public static function createScanner(array $options)
    {
        switch ($options['strategy']) {
            case 'clamd_unix':
                $scanStrategy = new ScanStrategyClamdUnix(
                    $options['socket'] ?? ScanStrategyClamdUnix::DEFAULT_SOCKET
                );
                break;
            case 'clamd_network':
                $scanStrategy = new ScanStrategyClamdNetwork(
                    $options['host'] ?? ScanStrategyClamdNetwork::DEFAULT_HOST,
                    $options['port'] ?? ScanStrategyClamdNetwork::DEFAULT_PORT
                );
                break;
            default:
                throw new \Exception(sprintf('Unsupported scan strategy "%s" configured', $options['strategy']));
        }

        return new Scanner($scanStrategy);
    }
}

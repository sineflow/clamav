<?php

namespace Sineflow\ClamAV\Tests\Unit\Bundle;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Bundle\ScannerFactory;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;

class ScannerFactoryTest extends TestCase
{
    public function testCreatesClamdUnixScanner(): void
    {
        $scanner = ScannerFactory::createScanner(['strategy' => 'clamd_unix', 'socket' => null]);

        $this->assertInstanceOf(Scanner::class, $scanner);
        $this->assertInstanceOf(ScanStrategyClamdUnix::class, $this->getStrategy($scanner));
    }

    public function testCreatesClamdNetworkScanner(): void
    {
        $scanner = ScannerFactory::createScanner(['strategy' => 'clamd_network', 'host' => null, 'port' => null]);

        $this->assertInstanceOf(Scanner::class, $scanner);
        $this->assertInstanceOf(ScanStrategyClamdNetwork::class, $this->getStrategy($scanner));
    }

    public function testThrowsOnUnknownStrategy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported scan strategy "ftp" configured');

        ScannerFactory::createScanner(['strategy' => 'ftp']);
    }

    private function getStrategy(Scanner $scanner): object
    {
        $ref = new \ReflectionProperty(Scanner::class, 'scanStrategy');
        return $ref->getValue($scanner);
    }
}

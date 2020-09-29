<?php

namespace Sineflow\ClamAV\ScanStrategy\Tests;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;

class ScannerTest extends TestCase
{
    public function testPingWithClamdUnix()
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix());
        $this->assertTrue($scanner->ping());
    }

    public function testPingWithClamdNetwork()
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork());
        $this->assertTrue($scanner->ping());
    }

    public function testVersionWithClamdUnix()
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix());
        $this->assertIsString($scanner->version());
    }

    public function testVersionWithClamdNetwork()
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork());
        $this->assertIsString($scanner->version());
    }


    /**
     * @dataProvider validFilesToCheckProvider
     */
    public function testScanValidFilesWithClamdUnix(string $filePath, bool $expectedVirus, string $expectedVirusName)
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix());

        $scanResult = $scanner->scan($filePath);
        $this->assertSame($expectedVirus, !$scanResult->isClean());
        $this->assertSame($expectedVirusName, $scanResult->getVirusName());
        $this->assertSame($filePath, $scanResult->getFileName());
    }

    /**
     * @dataProvider invalidFilesToCheckProvider
     */
    public function testScanInvalidFilesWithClamdUnix(string $filePath)
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix());

        $this->expectException(\RuntimeException::class);
        $scanner->scan($filePath);
    }
    /**
     * @dataProvider validFilesToCheckProvider
     */
    public function testScanValidFilesWithClamdNetwork(string $filePath, bool $expectedVirus, string $expectedVirusName)
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork());

        $scanResult = $scanner->scan($filePath);
        $this->assertSame($expectedVirus, !$scanResult->isClean());
        $this->assertSame($expectedVirusName, $scanResult->getVirusName());
        $this->assertSame($filePath, $scanResult->getFileName());
    }

    /**
     * @dataProvider invalidFilesToCheckProvider
     */
    public function testScanInvalidFilesWithClamdNetwork(string $filePath)
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork());

        $this->expectException(\RuntimeException::class);
        $scanner->scan($filePath);
    }

    public function validFilesToCheckProvider()
    {
        return [
            [__DIR__.'/../Files/clean.txt', false, ''],
            [__DIR__.'/../Files/eicar.txt', true, 'Win.Test.EICAR_HDB-1'],
            [__DIR__.'/../Files/eicar-dropper.pdf', true, 'Doc.Dropper.Agent-1540415'],
            [__DIR__.'/../Files/infected-archive.zip', true, 'Win.Test.EICAR_HDB-1'],
        ];
    }

    public function invalidFilesToCheckProvider()
    {
        return [
            [__DIR__.'/../Files/'],
            ['file_does_not_exist'],
        ];
    }
}

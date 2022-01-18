<?php

namespace Sineflow\ClamAV\ScanStrategy\Tests;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;

class ScannerTest extends TestCase
{
    private static $originalFilePermissionsOfInaccessibleFile;

    public static function setUpBeforeClass(): void
    {
        self::$originalFilePermissionsOfInaccessibleFile = fileperms(__DIR__.'/../Files/inaccessible.txt');
        chmod(__DIR__.'/../Files/inaccessible.txt', 0000);
    }

    public static function tearDownAfterClass(): void
    {
        chmod(__DIR__.'/../Files/inaccessible.txt', self::$originalFilePermissionsOfInaccessibleFile);
    }

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
    public function testScanInvalidFilesWithClamdUnix(string $filePath, string $expectedErrorMessage)
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix());

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
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
    public function testScanInvalidFilesWithClamdNetwork(string $filePath, string $expectedErrorMessage)
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork());

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
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
            [__DIR__.'/../Files/', 'Error scanning "'.__DIR__.'/../Files/'.'": Not a file.'],
            ['file_does_not_exist', 'Error scanning "file_does_not_exist": Not a file.'],
            [__DIR__.'/../Files/inaccessible.txt', 'Error scanning "'.__DIR__.'/../Files/inaccessible.txt'.'": Access denied.'],
        ];
    }
}

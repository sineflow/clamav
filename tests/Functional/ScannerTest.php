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
    private static $socket;
    private static $host;
    private static $port;

    public static function setUpBeforeClass(): void
    {
        self::$socket = getenv('CLAMAV_SOCKET') ?: ScanStrategyClamdUnix::DEFAULT_SOCKET;
        self::$host = getenv('CLAMAV_HOST') ?: ScanStrategyClamdNetwork::DEFAULT_HOST;
        self::$port = getenv('CLAMAV_PORT') ?: ScanStrategyClamdNetwork::DEFAULT_PORT;

        self::$originalFilePermissionsOfInaccessibleFile = fileperms(__DIR__.'/../Files/inaccessible.txt');
        chmod(__DIR__.'/../Files/inaccessible.txt', 0000);
    }

    public static function tearDownAfterClass(): void
    {
        chmod(__DIR__.'/../Files/inaccessible.txt', self::$originalFilePermissionsOfInaccessibleFile);
    }

    public function testPingWithClamdUnix()
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));
        $this->assertTrue($scanner->ping());
    }

    public function testPingWithClamdNetwork()
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));
        $this->assertTrue($scanner->ping());
    }

    public function testVersionWithClamdUnix()
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));
        $this->assertIsString($scanner->version());
    }

    public function testVersionWithClamdNetwork()
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));
        $this->assertIsString($scanner->version());
    }


    /**
     * @dataProvider validFilesToCheckProvider
     */
    public function testScanValidFilesWithClamdUnix(string $filePath, bool $expectedVirus, string $expectedVirusName)
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));

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
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $scanner->scan($filePath);
    }

    /**
     * @dataProvider validFilesToCheckProvider
     */
    public function testScanValidFilesWithClamdNetwork(string $filePath, bool $expectedVirus, string $expectedVirusName)
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));

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
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $scanner->scan($filePath);
    }

    public function validFilesToCheckProvider()
    {
        return [
            [realpath(__DIR__.'/../Files/clean.txt'), false, ''],
            [realpath(__DIR__.'/../Files/eicar.txt'), true, 'Win.Test.EICAR_HDB-1'],
            [realpath(__DIR__.'/../Files/eicar-dropper.pdf'), true, 'Doc.Dropper.Agent-1540415'],
            [realpath(__DIR__.'/../Files/infected-archive.zip'), true, 'Win.Test.EICAR_HDB-1'],
        ];
    }

    public function invalidFilesToCheckProvider()
    {
        return [
            [realpath(__DIR__.'/../Files/'), 'Error scanning "'.realpath(__DIR__.'/../Files/').'": Not a file.'],
            ['file_does_not_exist', 'Error scanning "file_does_not_exist": Not a file.'],
            [realpath(__DIR__.'/../Files/inaccessible.txt'), 'Error scanning "'.realpath(__DIR__.'/../Files/inaccessible.txt').'": Access denied.'],
        ];
    }
}

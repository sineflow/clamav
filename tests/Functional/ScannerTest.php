<?php

namespace Sineflow\ClamAV\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdNetwork;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;

class ScannerTest extends TestCase
{
    private static string $socket;
    private static string $host;
    private static int $port;
    private static int $originalFilePermissionsOfInaccessibleFile;
    private static bool $filePermissionsCanBeEnforced;

    public static function setUpBeforeClass(): void
    {
        self::$socket = getenv('CLAMAV_SOCKET') ?: ScanStrategyClamdUnix::DEFAULT_SOCKET;
        self::$host = getenv('CLAMAV_HOST') ?: ScanStrategyClamdNetwork::DEFAULT_HOST;
        self::$port = (int) (getenv('CLAMAV_PORT') ?: ScanStrategyClamdNetwork::DEFAULT_PORT);

        self::$originalFilePermissionsOfInaccessibleFile = fileperms(__DIR__.'/../Files/inaccessible.txt');
        chmod(__DIR__.'/../Files/inaccessible.txt', 0000);
        self::$filePermissionsCanBeEnforced = !is_readable(__DIR__.'/../Files/inaccessible.txt');
    }

    public static function tearDownAfterClass(): void
    {
        chmod(__DIR__.'/../Files/inaccessible.txt', self::$originalFilePermissionsOfInaccessibleFile);
    }

    public function testPingWithClamdUnix(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));
        $this->assertTrue($scanner->ping());
    }

    public function testPingWithClamdNetwork(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));
        $this->assertTrue($scanner->ping());
    }

    public function testVersionWithClamdUnix(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));
        $this->assertIsString($scanner->version());
    }

    public function testVersionWithClamdNetwork(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));
        $this->assertIsString($scanner->version());
    }

    public function testMultipleCommandsOnSameInstanceWithClamdUnix(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));

        $this->assertTrue($scanner->ping());
        $this->assertIsString($scanner->version());
        $this->assertTrue($scanner->scan(realpath(__DIR__.'/../Files/clean.txt'))->isClean());
        $this->assertFalse($scanner->scan(realpath(__DIR__.'/../Files/eicar.txt'))->isClean());
    }

    public function testMultipleCommandsOnSameInstanceWithClamdNetwork(): void
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));

        $this->assertTrue($scanner->ping());
        $this->assertIsString($scanner->version());
        $this->assertTrue($scanner->scan(realpath(__DIR__.'/../Files/clean.txt'))->isClean());
        $this->assertFalse($scanner->scan(realpath(__DIR__.'/../Files/eicar.txt'))->isClean());
    }

    #[DataProvider('validFilesToCheckProvider')]
    public function testScanValidFilesWithClamdUnix(string $filePath, bool $expectedVirus, string $expectedVirusName): void
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));

        $scanResult = $scanner->scan($filePath);
        $this->assertSame($expectedVirus, !$scanResult->isClean());
        $this->assertSame($expectedVirusName, $scanResult->getVirusName());
        $this->assertSame($filePath, $scanResult->getFileName());
    }

    #[DataProvider('invalidFilesToCheckProvider')]
    public function testScanInvalidFilesWithClamdUnix(string $filePath, string $expectedErrorMessage): void
    {
        if ($filePath === realpath(__DIR__.'/../Files/inaccessible.txt') && !self::$filePermissionsCanBeEnforced) {
            $this->markTestSkipped('File permissions cannot be enforced on this filesystem (e.g., running as root in Docker).');
        }

        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $scanner->scan($filePath);
    }

    #[DataProvider('validFilesToCheckProvider')]
    public function testScanValidFilesWithClamdNetwork(string $filePath, bool $expectedVirus, string $expectedVirusName): void
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));

        $scanResult = $scanner->scan($filePath);
        $this->assertSame($expectedVirus, !$scanResult->isClean());
        $this->assertSame($expectedVirusName, $scanResult->getVirusName());
        $this->assertSame($filePath, $scanResult->getFileName());
    }

    #[DataProvider('invalidFilesToCheckProvider')]
    public function testScanInvalidFilesWithClamdNetwork(string $filePath, string $expectedErrorMessage): void
    {
        if ($filePath === realpath(__DIR__.'/../Files/inaccessible.txt') && !self::$filePermissionsCanBeEnforced) {
            $this->markTestSkipped('File permissions cannot be enforced on this filesystem (e.g., running as root in Docker).');
        }

        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));

        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $scanner->scan($filePath);
    }

    #[DataProvider('validStreamFilesToCheckProvider')]
    public function testScanStreamWithClamdUnix(string $filePath, bool $expectedVirus, string $expectedVirusName): void
    {
        $scanner = new Scanner(new ScanStrategyClamdUnix(self::$socket));
        $stream = fopen($filePath, 'rb');

        try {
            $scanResult = $scanner->scanStream($stream, $filePath);
            $this->assertSame($expectedVirus, !$scanResult->isClean());
            $this->assertSame($expectedVirusName, $scanResult->getVirusName());
            $this->assertSame($filePath, $scanResult->getFileName());
        } finally {
            fclose($stream);
        }
    }

    #[DataProvider('validStreamFilesToCheckProvider')]
    public function testScanStreamWithClamdNetwork(string $filePath, bool $expectedVirus, string $expectedVirusName): void
    {
        $scanner = new Scanner(new ScanStrategyClamdNetwork(self::$host, self::$port));
        $stream = fopen($filePath, 'rb');

        try {
            $scanResult = $scanner->scanStream($stream, $filePath);
            $this->assertSame($expectedVirus, !$scanResult->isClean());
            $this->assertSame($expectedVirusName, $scanResult->getVirusName());
            $this->assertSame($filePath, $scanResult->getFileName());
        } finally {
            fclose($stream);
        }
    }

    public static function validFilesToCheckProvider(): array
    {
        return [
            [realpath(__DIR__.'/../Files/clean.txt'), false, ''],
            [realpath(__DIR__.'/../Files/eicar.txt'), true, 'Eicar-Test-Signature'],
            [realpath(__DIR__.'/../Files/eicar-dropper.pdf'), true, 'Pdf.Dropper.Agent-6299400-0'],
            [realpath(__DIR__.'/../Files/infected-archive.zip'), true, 'Eicar-Test-Signature'],
        ];
    }

    public static function invalidFilesToCheckProvider(): array
    {
        return [
            [realpath(__DIR__.'/../Files/'), 'Error scanning "'.realpath(__DIR__.'/../Files/').'": Not a file.'],
            ['file_does_not_exist', 'Error scanning "file_does_not_exist": Not a file.'],
            [realpath(__DIR__.'/../Files/inaccessible.txt'), 'Error scanning "'.realpath(__DIR__.'/../Files/inaccessible.txt').'": Access denied.'],
        ];
    }

    public static function validStreamFilesToCheckProvider(): array
    {
        return [
            [realpath(__DIR__.'/../Files/clean.txt'), false, ''],
            [realpath(__DIR__.'/../Files/eicar.txt'), true, 'Eicar-Test-Signature'],
            [realpath(__DIR__.'/../Files/eicar-dropper.pdf'), true, 'Pdf.Dropper.Agent-6299400-0'],
            [realpath(__DIR__.'/../Files/infected-archive.zip'), true, 'Eicar-Test-Signature'],
        ];
    }
}

<?php

namespace Sineflow\ClamAV\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyInterface;

class ScannerTest extends TestCase
{
    public function testScanDelegatesToStrategy(): void
    {
        $expected = ScannedFile::fromRawResponse('/path/to/file', '/path/to/file: OK');

        $strategy = $this->createMock(ScanStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('scan')
            ->with('/path/to/file')
            ->willReturn($expected);

        $scanner = new Scanner($strategy);
        $result = $scanner->scan('/path/to/file');

        $this->assertSame($expected, $result);
    }

    public function testPingDelegatesToStrategy(): void
    {
        $strategy = $this->createMock(ScanStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $scanner = new Scanner($strategy);
        $this->assertTrue($scanner->ping());
    }

    public function testVersionDelegatesToStrategy(): void
    {
        $strategy = $this->createMock(ScanStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('version')
            ->willReturn('ClamAV 1.0.0');

        $scanner = new Scanner($strategy);
        $this->assertSame('ClamAV 1.0.0', $scanner->version());
    }
}

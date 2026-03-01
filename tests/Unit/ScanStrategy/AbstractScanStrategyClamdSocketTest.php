<?php

namespace Sineflow\ClamAV\Tests\Unit\ScanStrategy;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\ScanStrategy\AbstractScanStrategyClamdSocket;
use Sineflow\ClamAV\Socket\Socket;

class AbstractScanStrategyClamdSocketTest extends TestCase
{
    private Socket $socket;
    private AbstractScanStrategyClamdSocket $strategy;

    protected function setUp(): void
    {
        $this->socket = $this->createMock(Socket::class);

        $this->strategy = new class ($this->socket) extends AbstractScanStrategyClamdSocket {
            public function __construct(Socket $socket)
            {
                $this->socket = $socket;
            }
        };
    }

    public function testScanSendsCorrectCommand(): void
    {
        $filePath = __FILE__;

        $this->socket->expects($this->once())
            ->method('sendCommand')
            ->with('SCAN ' . $filePath)
            ->willReturn($filePath . ': OK');

        $result = $this->strategy->scan($filePath);

        $this->assertTrue($result->isClean());
        $this->assertSame($filePath, $result->getFileName());
    }

    public function testScanRejectsNonFile(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Not a file.');

        $this->strategy->scan('/non/existent/path');
    }

    public function testScanRejectsDirectory(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Not a file.');

        $this->strategy->scan(__DIR__);
    }

    public function testPingReturnsTrue(): void
    {
        $this->socket->expects($this->once())
            ->method('sendCommand')
            ->with('PING')
            ->willReturn('PONG');

        $this->assertTrue($this->strategy->ping());
    }

    public function testPingReturnsFalseOnUnexpectedResponse(): void
    {
        $this->socket->expects($this->once())
            ->method('sendCommand')
            ->with('PING')
            ->willReturn('UNEXPECTED');

        $this->assertFalse($this->strategy->ping());
    }

    public function testVersionReturnsResponse(): void
    {
        $this->socket->expects($this->once())
            ->method('sendCommand')
            ->with('VERSION')
            ->willReturn("ClamAV 1.0.0\n");

        $this->assertSame('ClamAV 1.0.0', $this->strategy->version());
    }
}

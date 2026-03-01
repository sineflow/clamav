<?php

namespace Sineflow\ClamAV\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\SocketException;

class SocketExceptionTest extends TestCase
{
    public function testWithoutErrorCode(): void
    {
        $e = new SocketException('Connection failed');

        $this->assertSame('Connection failed', $e->getMessage());
        $this->assertNull($e->getErrorCode());
    }

    public function testWithErrorCode(): void
    {
        if (!\defined('SOCKET_ECONNREFUSED')) {
            $this->markTestSkipped('SOCKET_ECONNREFUSED not defined on this platform.');
        }

        $code = SOCKET_ECONNREFUSED;
        $e = new SocketException('Connection failed', $code);

        $this->assertSame($code, $e->getErrorCode());
        $this->assertStringContainsString('Connection failed', $e->getMessage());
        $this->assertStringContainsString((string) $code, $e->getMessage());
        $this->assertStringContainsString(socket_strerror($code), $e->getMessage());
    }

    public function testIsRuntimeException(): void
    {
        $this->assertInstanceOf(\RuntimeException::class, new SocketException('error'));
    }
}

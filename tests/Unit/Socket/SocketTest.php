<?php

namespace Sineflow\ClamAV\Tests\Unit\Socket;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\SocketException;
use Sineflow\ClamAV\Socket\Socket;

class SocketTest extends TestCase
{
    public function testSocketTimeoutThrowsOnUnresponsiveServer(): void
    {
        $server = socket_create(AF_INET, SOCK_STREAM, 0);
        $this->assertNotFalse($server, 'Failed to create server socket');

        $this->assertNotFalse(socket_bind($server, '127.0.0.1', 0), 'Failed to bind server socket');
        $this->assertNotFalse(socket_listen($server), 'Failed to listen on server socket');

        $this->assertNotFalse(socket_getsockname($server, $address, $port), 'Failed to get server socket name');

        try {
            $socket = new Socket(Socket::NETWORK, [$address, $port], timeoutSeconds: 1);

            $this->expectException(SocketException::class);
            $this->expectExceptionMessageMatches('/^Reading from socket failed/');

            $socket->sendCommand('PING');
        } finally {
            socket_close($server);
        }
    }

    public function testSendCommandTrimsTrailingNullByte(): void
    {
        $this->assertTrue(socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $pair), 'Failed to create socket pair');

        $socket = new Socket(Socket::NETWORK, ['127.0.0.1', 3310]);
        $this->injectInternalSocket($socket, $pair[0]);

        try {
            $this->assertNotFalse(socket_write($pair[1], "PONG\0"), 'Failed to write response');
            $this->assertTrue(socket_shutdown($pair[1], 1), 'Failed to shutdown server write side');

            $response = $socket->sendCommand('PING');

            $this->assertSame('PONG', $response);
            $this->assertSame('PING', $this->readAllFromSocket($pair[1]));
        } finally {
            socket_close($pair[1]);
        }
    }

    public function testSendInstreamFromStreamSendsExpectedProtocolFrames(): void
    {
        $this->assertTrue(socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $pair), 'Failed to create socket pair');

        $socket = new Socket(Socket::NETWORK, ['127.0.0.1', 3310]);
        $this->injectInternalSocket($socket, $pair[0]);

        $stream = fopen('php://memory', 'r+');
        $this->assertNotFalse($stream);
        fwrite($stream, 'abc');
        rewind($stream);

        try {
            $this->assertNotFalse(socket_write($pair[1], "stream: OK\0"), 'Failed to write response');
            $this->assertTrue(socket_shutdown($pair[1], 1), 'Failed to shutdown server write side');

            $response = $socket->sendInstreamFromStream($stream);
            $payload = $this->readAllFromSocket($pair[1]);

            $this->assertSame('stream: OK', $response);
            $this->assertSame("zINSTREAM\0" . pack('N', 3) . 'abc' . pack('N', 0), $payload);
        } finally {
            fclose($stream);
            socket_close($pair[1]);
        }
    }

    private function injectInternalSocket(Socket $socket, \Socket $connectedSocket): void
    {
        $reflection = new \ReflectionClass($socket);
        $property = $reflection->getProperty('socket');
        $property->setValue($socket, $connectedSocket);
    }

    private function readAllFromSocket(\Socket $socket): string
    {
        $result = '';

        do {
            $bytes = socket_recv($socket, $chunk, 8192, 0);
            $this->assertNotFalse($bytes);
            $result .= $chunk;
        } while ($bytes > 0);

        return $result;
    }
}

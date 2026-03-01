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
}

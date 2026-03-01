<?php

namespace Sineflow\ClamAV\Socket;

use Sineflow\ClamAV\Exception\SocketException;

class Socket
{
    public const UNIX = \AF_UNIX;
    public const NETWORK = \AF_INET;

    private const MAX_READ_BYTES = 8192;

    private ?\Socket $socket = null;

    public function __construct(
        private readonly int $socketType,
        private readonly array $connectionArguments,
        private readonly ?int $timeoutSeconds = null,
    ) {
    }

    public function sendCommand(string $dataIn, int $flagsSend = 0, int $flagsReceive = MSG_WAITALL): string
    {
        $this->connect();

        if (false === socket_send($this->socket, $dataIn, strlen($dataIn), $flagsSend)) {
            $this->closeAndThrow('Writing to socket failed');
        }
        $dataOut = '';

        do {
            $bytes = socket_recv($this->socket, $chunk, self::MAX_READ_BYTES, $flagsReceive);
            if (false === $bytes) {
                $this->closeAndThrow('Reading from socket failed');
            }
            $dataOut .= $chunk;
        } while ($bytes);
        socket_close($this->socket);
        $this->socket = null;

        return $dataOut;
    }

    /**
     * @throws SocketException
     */
    private function connect(): void
    {
        if ($this->socket instanceof \Socket) {
            return;
        }

        $socket = @ socket_create($this->socketType, SOCK_STREAM, 0);
        if ($socket === false) {
            $this->closeAndThrow('Creating socket failed');
        }
        $this->socket = $socket;

        $success = @ socket_connect($this->socket, ...$this->connectionArguments);
        if ($success === false) {
            $this->closeAndThrow('Connecting to socket failed');
        }

        if ($this->timeoutSeconds !== null) {
            $timeout = ['sec' => $this->timeoutSeconds, 'usec' => 0];
            if (false === socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeout)) {
                $this->closeAndThrow('Setting socket send timeout failed');
            }
            if (false === socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeout)) {
                $this->closeAndThrow('Setting socket receive timeout failed');
            }
        }
    }

    private function closeAndThrow(string $message): never
    {
        $errorCode = socket_last_error($this->socket);
        if (null !== $this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
        throw new SocketException($message, $errorCode);
    }
}

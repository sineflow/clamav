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

    /**
     * @throws SocketException
     */
    public function sendCommand(string $dataIn, int $flagsSend = 0, int $flagsReceive = MSG_WAITALL): string
    {
        $this->connect();

        $this->sendAll($dataIn, $flagsSend);

        return $this->readAndClose($flagsReceive);
    }

    /**
     * Stream data from an already-open resource to ClamAV via INSTREAM protocol.
     * The caller owns the stream lifecycle (open/close).
     *
     * @param resource $stream Open readable stream
     *
     * @throws SocketException
     */
    public function sendInstreamFromStream($stream, int $flagsSend = 0, int $flagsReceive = MSG_WAITALL): string
    {
        $this->connect();

        // Send zINSTREAM\0 command
        $command = "zINSTREAM\0";
        $this->sendAll($command, $flagsSend);

        while (!feof($stream)) {
            $chunk = fread($stream, self::MAX_READ_BYTES);
            if ($chunk === false) {
                $this->closeAndThrow('Reading from stream failed');
            }
            if ($chunk === '') {
                break;
            }
            $chunkLen = strlen($chunk);
            $header = pack('N', $chunkLen);
            $data = $header . $chunk;
            $this->sendAll($data, $flagsSend);
        }

        // Send terminator (4 zero bytes)
        $terminator = pack('N', 0);
        $this->sendAll($terminator, $flagsSend);

        return $this->readAndClose($flagsReceive);
    }

    /**
     * @throws SocketException
     */
    private function readAndClose(int $flagsReceive): string
    {
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

        return rtrim($dataOut, "\0");
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

    /**
     * Loop socket_send() until all bytes are written or an error occurs.
     *
     * @throws SocketException
     */
    private function sendAll(string $data, int $flags): void
    {
        $length = strlen($data);
        $offset = 0;

        while ($offset < $length) {
            $sent = socket_send($this->socket, substr($data, $offset), $length - $offset, $flags);
            if (false === $sent || $sent === 0) {
                $this->closeAndThrow('Writing to socket failed');
            }
            $offset += $sent;
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

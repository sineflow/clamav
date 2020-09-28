<?php

namespace Sineflow\ClamAV\Socket;

use Sineflow\ClamAV\Exception\SocketException;

class Socket
{
    const UNIX = \AF_UNIX;
    const NETWORK = \AF_INET;

    const MAX_READ_BYTES = 8192;

    /**
     * @var int
     */
    private $socketType;

    /**
     * @var array
     */
    private $connectionArguments;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @param int   $socketType
     * @param array $connectionArguments
     */
    public function __construct(int $socketType, array $connectionArguments)
    {
        $this->socketType = $socketType;
        $this->connectionArguments = $connectionArguments;
    }

    /**
     * @param string $dataIn
     * @param int    $flagsSend
     * @param int    $flagsReceive
     *
     * @return string
     */
    public function sendCommand($dataIn, $flagsSend = 0, $flagsReceive = MSG_WAITALL)
    {
        $this->connect();

        if (false === socket_send($this->socket, $dataIn, strlen($dataIn), $flagsSend)) {
            throw new SocketException('Writing to socket failed');
        }
        $dataOut = '';
        while ($bytes = socket_recv($this->socket, $chunk, self::MAX_READ_BYTES, $flagsReceive)) {
            if (false === $bytes) {
                throw new SocketException('Reading from socket failed', socket_last_error());
            }
            $dataOut .= $chunk;
        }
        socket_close($this->socket);

        return $dataOut;
    }

    /**
     * @throws SocketException
     */
    private function connect()
    {
        if (!is_resource($this->socket)) {
            $this->socket = @ socket_create($this->socketType, SOCK_STREAM, 0);
            if ($this->socket === false) {
                throw new SocketException('Creating socket failed', socket_last_error());
            }
            $hasError = @ socket_connect($this->socket, ...$this->connectionArguments);
            if ($hasError === false) {
                throw new SocketException('Connecting to socket failed', socket_last_error());
            }
        }
    }
}

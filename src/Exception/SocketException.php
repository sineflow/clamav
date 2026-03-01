<?php

namespace Sineflow\ClamAV\Exception;

class SocketException extends \RuntimeException
{
    private readonly ?int $errorCode;

    public function __construct(string $message, ?int $socketErrorCode = null)
    {
        $this->errorCode = $socketErrorCode;
        if ($socketErrorCode !== null) {
            $message = sprintf('%s: (%s) %s', $message, $socketErrorCode, socket_strerror($socketErrorCode));
        }

        parent::__construct($message);
    }

    /**
     * Get socket error (returned from 'socket_last_error')
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }
}

<?php

namespace Sineflow\ClamAV\Exception;

class SocketException extends \RuntimeException
{
    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @param string $message
     * @param int $socketErrorCode
     */
    public function __construct($message, int $socketErrorCode = null)
    {
        $this->errorCode = $socketErrorCode;
        if ($socketErrorCode) {
            $message = sprintf('%s: (%s) %s', $message, $socketErrorCode, socket_strerror($socketErrorCode));
        }

        parent::__construct($message);
    }

    /**
     * Get socket error (returned from 'socket_last_error')
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}

<?php

namespace Sineflow\ClamAV\Exception;

class FileScanException extends \RuntimeException
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param string $fileName
     * @param string $errorMessage
     */
    public function __construct(string $fileName, string $errorMessage)
    {
        $this->fileName = $fileName;
        $this->errorMessage = $errorMessage;
        $message = sprintf('Error scanning "%s": %s', $fileName, $errorMessage);

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}

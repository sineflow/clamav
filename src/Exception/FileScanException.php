<?php

namespace Sineflow\ClamAV\Exception;

class FileScanException extends \RuntimeException
{
    public function __construct(
        private readonly string $fileName,
        private readonly string $errorMessage,
    ) {
        parent::__construct(sprintf('Error scanning "%s": %s', $fileName, $errorMessage));
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}

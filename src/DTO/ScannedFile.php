<?php

namespace Sineflow\ClamAV\DTO;

use Sineflow\ClamAV\Exception\FileScanException;

class ScannedFile
{
    /**
     * @var string
     */
    private $rawResponse;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var bool
     */
    private $isClean;

    /**
     * @var string
     */
    private $virusName;

    /**
     * @param string $rawResponse
     */
    public function __construct(string $rawResponse)
    {
        $isParsed = preg_match('/(.*):(.*)(FOUND|OK|ERROR)/i', $rawResponse, $matches);

        if (!$isParsed) {
            throw new \RuntimeException(sprintf('Failed to parse clamav response: %s', $rawResponse));
        }

        if ($matches[3] === 'ERROR') {
            throw new FileScanException($matches[1], trim($matches[2]));
        }

        $this->rawResponse = $rawResponse;
        $this->fileName = $matches[1];
        $this->virusName = trim($matches[2]);
        $this->isClean = ($matches[3] === 'OK');
    }

    /**
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return bool
     */
    public function isClean(): bool
    {
        return $this->isClean;
    }

    /**
     * @return string
     */
    public function getVirusName(): string
    {
        return $this->virusName;
    }
}

<?php

namespace Sineflow\ClamAV\DTO;

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
        $this->rawResponse = $rawResponse;

        preg_match('/(.*):(.*)(FOUND|OK)/i', $rawResponse, $matches);
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

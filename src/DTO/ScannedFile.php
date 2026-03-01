<?php

namespace Sineflow\ClamAV\DTO;

use Sineflow\ClamAV\Exception\FileScanException;

class ScannedFile
{
    private function __construct(
        private readonly string $rawResponse,
        private readonly string $fileName,
        private readonly bool $isClean,
        private readonly string $virusName,
    ) {
    }

    public static function fromRawResponse(string $filePath, string $rawResponse): self
    {
        $isParsed = preg_match('/^(.+): (.*?)\s*(FOUND|OK|ERROR)\s*$/', $rawResponse, $matches);

        if (!$isParsed) {
            throw new FileScanException($filePath, sprintf('Failed to parse clamav response: %s', $rawResponse));
        }

        if ($matches[1] !== $filePath) {
            throw new FileScanException($filePath, sprintf('Parsed file path "%s" does not match the provided file path.', $matches[1]));
        }

        if ($matches[3] === 'ERROR') {
            throw new FileScanException($filePath, trim($matches[2]));
        }

        return new self(
            rawResponse: $rawResponse,
            fileName: $filePath,
            isClean: ($matches[3] === 'OK'),
            virusName: trim($matches[2]),
        );
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function isClean(): bool
    {
        return $this->isClean;
    }

    public function getVirusName(): string
    {
        return $this->virusName;
    }
}

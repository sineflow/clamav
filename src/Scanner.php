<?php

namespace Sineflow\ClamAV;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyInterface;

class Scanner
{
    public function __construct(private readonly ScanStrategyInterface $scanStrategy)
    {
    }

    public function scan(string $filePath): ScannedFile
    {
        return $this->scanStrategy->scan($filePath);
    }

    /**
     * @param resource $stream   Open readable stream
     * @param string   $fileName Identifier for logging/error messages
     */
    public function scanStream($stream, string $fileName): ScannedFile
    {
        return $this->scanStrategy->scanStream($stream, $fileName);
    }

    public function ping(): bool
    {
        return $this->scanStrategy->ping();
    }

    public function version(): string
    {
        return $this->scanStrategy->version();
    }
}

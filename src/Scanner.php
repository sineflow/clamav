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

    public function ping(): bool
    {
        return $this->scanStrategy->ping();
    }

    public function version(): string
    {
        return $this->scanStrategy->version();
    }
}

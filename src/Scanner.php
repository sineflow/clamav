<?php

namespace Sineflow\ClamAV;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyInterface;

class Scanner
{
    /**
     * @var ScanStrategyInterface
     */
    public $scanStrategy;

    /**
     * @param ScanStrategyInterface $scanStrategy
     */
    public function __construct(ScanStrategyInterface $scanStrategy)
    {
        $this->scanStrategy = $scanStrategy;
    }

    /**
     * @param string $filePath
     *
     * @return ScannedFile
     */
    public function scan(string $filePath): ScannedFile
    {
        return $this->scanStrategy->scan($filePath);
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return $this->scanStrategy->ping();
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return $this->scanStrategy->version();
    }
}

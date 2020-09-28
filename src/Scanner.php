<?php

namespace Sineflow\ClamAV;

use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\DTO\ScanResult;
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
}

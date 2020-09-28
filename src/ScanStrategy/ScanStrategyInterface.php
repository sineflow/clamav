<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\DTO\ScannedFile;

interface ScanStrategyInterface
{
    public function scan(string $filePath): ScannedFile;

    public function version();

    public function ping();
}

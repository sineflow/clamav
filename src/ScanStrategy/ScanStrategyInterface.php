<?php

namespace Sineflow\ClamAV\ScanStrategy;

use Sineflow\ClamAV\DTO\ScannedFile;

interface ScanStrategyInterface
{
    public function scan(string $filePath): ScannedFile;

    /**
     * Scan an already-open stream via INSTREAM protocol.
     *
     * @param resource $stream   Open readable stream
     * @param string   $fileName Identifier for logging/error messages
     */
    public function scanStream($stream, string $fileName): ScannedFile;

    public function version(): string;

    public function ping(): bool;
}

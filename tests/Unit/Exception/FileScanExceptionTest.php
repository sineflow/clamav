<?php

namespace Sineflow\ClamAV\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Exception\FileScanException;

class FileScanExceptionTest extends TestCase
{
    public function testGetters(): void
    {
        $e = new FileScanException('/path/to/file.txt', 'Access denied.');

        $this->assertSame('/path/to/file.txt', $e->getFileName());
        $this->assertSame('Access denied.', $e->getErrorMessage());
        $this->assertSame('Error scanning "/path/to/file.txt": Access denied.', $e->getMessage());
    }

    public function testIsRuntimeException(): void
    {
        $e = new FileScanException('file.txt', 'Not a file.');

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }
}
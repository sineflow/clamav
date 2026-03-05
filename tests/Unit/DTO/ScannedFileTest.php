<?php

namespace Sineflow\ClamAV\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\DTO\ScannedFile;
use Sineflow\ClamAV\Exception\FileScanException;

class ScannedFileTest extends TestCase
{
    public function testCleanFile(): void
    {
        $raw = '/path/to/file: OK';
        $sf = ScannedFile::fromRawResponse('/path/to/file', $raw);

        $this->assertSame($raw, $sf->getRawResponse());
        $this->assertSame('/path/to/file', $sf->getFileName());
        $this->assertTrue($sf->isClean());
        $this->assertSame('', $sf->getVirusName());
    }

    public function testInfectedFile(): void
    {
        $raw = '/path/to/file: Eicar-Test-Signature FOUND';
        $sf = ScannedFile::fromRawResponse('/path/to/file', $raw);

        $this->assertSame($raw, $sf->getRawResponse());
        $this->assertSame('/path/to/file', $sf->getFileName());
        $this->assertFalse($sf->isClean());
        $this->assertSame('Eicar-Test-Signature', $sf->getVirusName());
    }

    public function testErrorResponseThrowsFileScanException(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Error scanning "/path/to/file": Access denied.');

        ScannedFile::fromRawResponse('/path/to/file', '/path/to/file: Access denied. ERROR');
    }

    public function testErrorResponseExceptionCarriesFileNameAndMessage(): void
    {
        try {
            ScannedFile::fromRawResponse('/path/to/file', '/path/to/file: Access denied. ERROR');
            $this->fail('Expected FileScanException was not thrown.');
        } catch (FileScanException $e) {
            $this->assertSame('/path/to/file', $e->getFileName());
            $this->assertSame('Access denied.', $e->getErrorMessage());
        }
    }

    public function testUnparsableResponseThrowsRuntimeException(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Failed to parse clamav response: garbage');

        ScannedFile::fromRawResponse('/path/to/file', 'garbage');
    }

    public function testMismatchedFilePathThrowsFileScanException(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Parsed file path "/other/path" does not match the provided file path.');

        ScannedFile::fromRawResponse('/path/to/file', '/other/path: OK');
    }

    public function testInstreamCleanResponse(): void
    {
        $raw = 'stream: OK';
        $sf = ScannedFile::fromInstreamResponse('/path/to/file', $raw);

        $this->assertSame($raw, $sf->getRawResponse());
        $this->assertSame('/path/to/file', $sf->getFileName());
        $this->assertTrue($sf->isClean());
        $this->assertSame('', $sf->getVirusName());
    }

    public function testInstreamInfectedResponse(): void
    {
        $raw = 'stream: Eicar-Test-Signature FOUND';
        $sf = ScannedFile::fromInstreamResponse('/path/to/file', $raw);

        $this->assertSame($raw, $sf->getRawResponse());
        $this->assertSame('/path/to/file', $sf->getFileName());
        $this->assertFalse($sf->isClean());
        $this->assertSame('Eicar-Test-Signature', $sf->getVirusName());
    }

    public function testInstreamErrorResponseThrowsFileScanException(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Error scanning "/path/to/file": INSTREAM size limit exceeded.');

        ScannedFile::fromInstreamResponse('/path/to/file', 'stream: INSTREAM size limit exceeded. ERROR');
    }

    public function testInstreamUnparsableResponseThrowsException(): void
    {
        $this->expectException(FileScanException::class);
        $this->expectExceptionMessage('Failed to parse clamav response: garbage');

        ScannedFile::fromInstreamResponse('/path/to/file', 'garbage');
    }
}

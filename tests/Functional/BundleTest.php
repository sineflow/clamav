<?php

namespace Sineflow\ClamAV\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\Bundle\SineflowClamAVBundle;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class SineflowClamavTestingKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new SineflowClamAVBundle(),
        ];
    }
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}

class BundleTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new SineflowClamavTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $scanner = $container->get('sineflow.clamav.scanner');
        $this->assertInstanceOf(Scanner::class, $scanner);

        $ref = new \ReflectionProperty(Scanner::class, 'scanStrategy');
        $value = $ref->getValue($scanner);
        $this->assertInstanceOf(ScanStrategyClamdUnix::class, $value);
    }
}

<?php

namespace Sineflow\ClamAV\ScanStrategy\Tests;

use PHPUnit\Framework\TestCase;
use Sineflow\ClamAV\SineflowClamAVBundle;
use Sineflow\ClamAV\Scanner;
use Sineflow\ClamAV\ScanStrategy\ScanStrategyClamdUnix;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class SineflowClamavTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new SineflowClamAVBundle(),
        ];
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}

class ReflectionHelper
{
    public static function getProperty($object, $property)
    {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }
}

class BundleTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new SineflowClamavTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $scanner = $container->get('sineflow.clamav.scanner');
        $this->assertInstanceOf(Scanner::class, $scanner);

        $value = ReflectionHelper::getProperty($scanner, 'scanStrategy');
        $this->assertInstanceOf(ScanStrategyClamdUnix::class, $value);
    }
}

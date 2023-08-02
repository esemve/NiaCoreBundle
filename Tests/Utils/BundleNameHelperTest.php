<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Utils;

use Nia\CoreBundle\Test\TestCase;
use Nia\CoreBundle\Utils\BundleNameHelper;

class BundleNameHelperTest extends TestCase
{
    public function testGetClassNameFromFileName(): void
    {
        $helper = $this->createBundleNameHelper();

        $this->assertSame('NiaCoreBundle', $helper->getClassNameFromFileName('NiaCoreBundle.php'));
        $this->assertSame('TestFile', $helper->getClassNameFromFileName('TestFile.php'));
    }

    public function testGetNamespacedClassNameFromPath(): void
    {
        $helper = $this->createBundleNameHelper();

        $this->assertSame('\NiaCoreBundle\Enum\NiaCoreBundle', $helper->getNamespacedClassNameFromPath('NiaCoreBundle', 'vendor/nia/core-bundle', 'vendor/nia/core-bundle/NiaCoreBundle.php'));
    }

    public function testGetBundleNameByDirectory(): void
    {
        $helper = $this->createBundleNameHelper();

        $this->assertSame('Nia\MediaBundle', $helper->getBundleNameByDirectory('/vendor/nia/media-bundle/'));
        $this->assertSame('Nia\CoreBundle', $helper->getBundleNameByDirectory('/vendor/nia/core-bundle/'));
    }

    public function testGetBundleNameByDirectoryNotVendor(): void
    {
        $helper = $this->createBundleNameHelper();

        $this->assertSame('Nia\MediaBundle', $helper->getBundleNameByDirectory('/nia/MediaBundle/'));
        $this->assertSame('Nia\CoreBundle', $helper->getBundleNameByDirectory('/nia/CoreBundle/'));
    }

    public function testGetBundleNameByClass(): void
    {
        $helper = $this->createBundleNameHelper();

        $this->assertSame('NiaCoreBundle', $helper->getBundleNameByClass('Nia\CoreBundle\Controllers\AController'));
        $this->assertSame('NiaMediaBundle', $helper->getBundleNameByClass('Nia\MediaBundle\Enum\Almale'));
    }

    protected function createBundleNameHelper($isVendor = false): BundleNameHelper
    {
        return new BundleNameHelper($isVendor);
    }
}

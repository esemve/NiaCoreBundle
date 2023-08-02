<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Twig\Extension;

use Nia\CoreBundle\Test\TestCase;
use Nia\CoreBundle\Twig\Extension\Slugify;

class SlugifyTest extends TestCase
{
    public function testGetName(): void
    {
        $extension = $this->createExtension();
        $this->assertSame('slugify', $extension->getName());
    }

    public function testGetFunctions(): void
    {
        $extension = $this->createExtension();
        $this->assertCount(1, $extension->getFunctions());
    }

    public function testSlugify(): void
    {
        $extension = $this->createExtension();

        $this->assertSame('arvizturo-tukorfurogep', $extension->slugify('árvíztűrő-tükörfúrógép'));
        $this->assertSame('arvizturo-tukorfurogep', $extension->slugify('ÁRVÍZTŰRŐ-TÜKÖRFÚRÓGÉP'));
        $this->assertSame('ez-itt-egy-test', $extension->slugify('ez itt egy test   '));
        $this->assertSame('n-a', $extension->slugify(''));
    }

    protected function createExtension(): Slugify
    {
        return new Slugify();
    }
}

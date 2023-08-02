<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Twig\Extension;

use Nia\CoreBundle\Test\TestCase;
use Nia\CoreBundle\Twig\Extension\DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DateTimeTest extends TestCase
{
    public function testGetName(): void
    {
        $extension = $this->createExtension('hu');
        $this->assertSame('dateTime', $extension->getName());
    }

    public function testGetFunctions(): void
    {
        $extension = $this->createExtension('hu');
        $this->assertCount(1, $extension->getFunctions());
    }

    public function testDateTime(): void
    {
        $extension = $this->createExtension('hu');
        $date = new \DateTime('2011-01-01 10:00:00');

        $this->assertSame('2011. 01. 01. 10:00', $extension->dateTime($date));

        $extension = $this->createExtension('en');
        $date = new \DateTime('2011-01-01 10:00:00');

        $this->assertSame('01. 01. 2011. 10:00', $extension->dateTime($date));
    }

    protected function createExtension(string $locale): DateTime
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->any())->method('getLocale')->willReturn($locale);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        return new DateTime(
            $requestStack
        );
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Provider;

use Nia\CoreBundle\Collections\LocaleCollection;
use Nia\CoreBundle\Provider\LocaleProvider;
use Nia\CoreBundle\Test\TestCase;
use Nia\CoreBundle\ValueObject\Locale;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class LocaleProviderTest extends TestCase
{
    public function testGetAvailableLocales(): void
    {
        $provider = $this->createLocaleProvider();
        $locales = $provider->getAvailableLocales();

        $this->assertInstanceOf(LocaleCollection::class, $locales);
        $this->assertSame(3, $locales->count());

        $this->assertSame('en', $locales->get('en')->getCode());
        $this->assertSame('LOCALE@en', $locales->get('en')->getName());

        $this->assertSame('de', $locales->get('de')->getCode());
        $this->assertSame('LOCALE@de', $locales->get('de')->getName());
    }

    public function testProvideByKey(): void
    {
        $provider = $this->createLocaleProvider();

        $byKey = $provider->provideByKey('en');

        $this->assertInstanceOf(Locale::class, $byKey);
        $this->assertSame('en', $byKey->getCode());
        $this->assertSame('LOCALE@en', $byKey->getName());
    }

    /**
     * @expectedException \Nia\CoreBundle\Exception\NotFoundException
     */
    public function testProvideByKeyException(): void
    {
        $provider = $this->createLocaleProvider();

        $provider->provideByKey('xx');
    }

    public function testIsValid(): void
    {
        $provider = $this->createLocaleProvider();

        $this->assertTrue($provider->isValid('en'));
        $this->assertTrue($provider->isValid('de'));
        $this->assertTrue($provider->isValid('hu'));
        $this->assertFalse($provider->isValid('fr'));
    }

    public function createLocaleProvider(): LocaleProvider
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())->method('trans')->will($this->returnCallback(function ($string) { return $string; }));

        $requestStack = $this->createMock(RequestStack::class);

        $locales = ['hu', 'en', 'de'];

        return new LocaleProvider(
            $locales,
            $translator,
            $requestStack
        );
    }
}

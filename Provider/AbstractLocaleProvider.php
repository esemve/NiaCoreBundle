<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Provider;

use Nia\CoreBundle\Collections\LocaleCollection;
use Nia\CoreBundle\Exception\NotFoundException;
use Nia\CoreBundle\ValueObject\Locale;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractLocaleProvider
{
    /**
     * @var LocaleCollection
     */
    private $locales;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        array $locales,
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $localeArray = [];

        foreach ($locales as $locale) {
            $transKey = 'LOCALE@'.$locale;
            $localeArray[$locale] = new Locale($locale, $translator->trans($transKey));
        }

        $this->locales = new LocaleCollection($localeArray);
        $this->requestStack = $requestStack;
    }

    public function getCurrentLocale(): Locale
    {
        if ((!empty($this->requestStack)) && (!empty($this->requestStack->getMasterRequest()))) {
            $locale = $this->requestStack->getMasterRequest()->getLocale();
        }

        return $this->provideByKey($locale ?? $this->getDefaultLocale()->getCode());
    }

    public function getAvailableLocales(): LocaleCollection
    {
        return $this->locales;
    }

    public function provideByKey(string $key): Locale
    {
        $found = $this->locales->get($key);
        if (null === $found) {
            throw new NotFoundException();
        }

        return $found;
    }

    public function isValid(string $locale): bool
    {
        return $this->locales->has($locale);
    }

    public function getDefaultLocale(): Locale
    {
        return $this->locales->first();
    }
}

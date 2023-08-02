<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form;

use Nia\CoreBundle\Provider\LocaleProvider;
use Nia\CoreBundle\ValueObject\Locale;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PortalLocaleType extends AbstractType
{
    /**
     * @var LocaleProvider
     */
    protected $localeProvider;

    public function setLocaleProvider(LocaleProvider $localeProvider): void
    {
        $this->localeProvider = $localeProvider;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'NiaCoreBundle@message.locale',
            'choices' => $this->createChoices(),
            'required' => false,
        ]);
    }

    protected function createChoices(): array
    {
        $locales = $this->localeProvider->getAvailableLocales();

        $output = [];
        /** @var Locale $locale */
        foreach ($locales as $locale) {
            $output[$locale->getName()] = $locale->getCode();
        }

        return $output;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    protected function callbackModelTransformer(): ?CallbackTransformer
    {
        return new CallbackTransformer(
        // Data to form value
            function ($arrayToString) {
                return $arrayToString;
            },
            // Form value to data
            function ($stringToArray) {
                return $stringToArray;
            }
        );
    }
}

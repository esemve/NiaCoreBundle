<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

class Language extends AbstractTwigExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'language';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('language', [$this, 'language'], [
            ]),
        ];
    }

    public function language(string $languageCode): string
    {
        return $this->translator->trans('LOCALE@'.mb_strtolower($languageCode));
    }
}

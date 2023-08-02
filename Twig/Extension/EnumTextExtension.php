<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

use MyCLabs\Enum\Enum;
use Symfony\Component\Translation\TranslatorInterface;

class EnumTextExtension extends AbstractTwigExtension
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
        return 'enumText';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('enumText', [$this, 'enumText'], [
            ]),
        ];
    }

    public function enumText(Enum $enum, string $transPrefix, string $locale = null, array $params = []): string
    {
        return $this->translator->trans($transPrefix.'.'.$enum->getKey(), $params, null, $locale);
    }
}

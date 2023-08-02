<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;

class DateTime extends AbstractTwigExtension
{
    /**
     * @var Request|null
     */
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getName()
    {
        return 'dateTime';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('dateTime', [$this, 'dateTime'], [
            ]),
        ];
    }

    public function dateTime(?\DateTime $dateTime): string
    {
        if (null === $dateTime) {
            return '-';
        }

        $format = 'Y. m. d. H:i';
        if ('hu' !== $this->request->getLocale()) {
            $format = 'd. m. Y. H:i';
        }

        return $dateTime->format($format);
    }
}

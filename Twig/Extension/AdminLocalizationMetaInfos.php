<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;

class AdminLocalizationMetaInfos extends AbstractTwigExtension
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
        return 'adminLocalizationMetaInfos';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('adminLocalizationMetaInfos', [$this, 'adminLocalizationMetaInfos'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function adminLocalizationMetaInfos(): string
    {
        return '<meta name="i18n-datetimeformat" content="Y-m-d H:i">';
    }
}

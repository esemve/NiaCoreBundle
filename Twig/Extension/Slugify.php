<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

class Slugify extends AbstractTwigExtension
{
    public function getName()
    {
        return 'slugify';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('slugify', [$this, 'slugify'], [
            ]),
        ];
    }

    public function slugify(string $text): string
    {
        // lowercase
        $text = mb_strtolower($text);

        $text = str_replace(['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű'], ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u'], $text);

        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

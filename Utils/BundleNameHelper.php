<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Utils;

class BundleNameHelper
{
    public function getClassNameFromFileName(string $fileName): string
    {
        return str_replace('.php', '', $fileName);
    }

    public function getNamespacedClassNameFromPath(string $bundle, string $directory, string $classRealPath): string
    {
        if (empty($bundle)) {
            $class = str_replace([$directory, '.php', '/'], '', $classRealPath);

            return 'App\\Enum\\'.$class;
        }

        return str_replace('/', '\\', str_replace('.php', '', '\\'.$bundle.'\\Enum'.str_replace($directory, '', $classRealPath)));
    }

    public function getBundleNameByDirectory(string $directory): string
    {
        if (false === mb_strpos($directory, 'nia')) {
            return '';
        }

        $bundleFolder = explode('/', explode('/nia/', $directory)[1])[0];

        return 'Nia\\'.ucfirst(str_replace('-bundle', 'Bundle', $bundleFolder));
    }

    public function getBundleNameByClass(string $class): string
    {
        $bundleName = explode('\\', $class);

        return $bundleName[0].$bundleName[1];
    }
}

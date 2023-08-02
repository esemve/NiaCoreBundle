<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Bundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractBundle extends Bundle
{
    protected function getEnv(ContainerBuilder $container): string
    {
        return $container->getParameter('kernel.environment');
    }

    protected function getAppType(ContainerBuilder $container): string
    {
        return $container->getParameter('kernel.app_type');
    }

    protected function getYamlLoader(string $currentDir, ContainerBuilder $container, ?string $env = ''): YamlFileLoader
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator($currentDir.'/Resources/config/'.$env)
        );

        return $loader;
    }

    protected function loadEnvs(string $currentDir, ContainerBuilder $container)
    {
        $appType = $this->getAppType($container);

        if (file_exists($currentDir.'/Resources/config/'.$appType)) {
            $loader = $this->getYamlLoader($currentDir, $container, $this->getAppType($container));

            if (file_exists($currentDir.'/Resources/config/'.$appType.'/config.yaml')) {
                $loader->load('config.yaml');
            }
            if (file_exists($currentDir.'/Resources/config/'.$appType.'/services.yaml')) {
                $loader->load('services.yaml');
            }
            $this->loadServicesByAppType($loader, $appType, $container);
        }
    }

    protected function loadServicesByAppType(YamlFileLoader $loader, string $appType, ContainerBuilder $container)
    {
    }
}

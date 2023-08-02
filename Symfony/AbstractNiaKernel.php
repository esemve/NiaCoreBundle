<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

abstract class AbstractNiaKernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    private $projectDir = null;

    private $applicationDir = null;

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->getAppName();
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log/'.$this->getAppName().'/';
    }

    public function registerBundles()
    {
        $contents = require $this->getGlobalApplicationPath().'/config/bundles.php';

        if (file_exists($this->getProjectDir().'/config/bundles.php')) {
            $contents = require $this->getProjectDir().'/config/bundles.php';
        }

        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getApplicationDir().'/config';
        $globalConfDir = $this->getGlobalApplicationPath().'/config';

        $loader->load($globalConfDir.'/*'.self::CONFIG_EXTS, 'glob');
        if (file_exists($globalConfDir.'/'.$this->environment)) {
            $loader->load($globalConfDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        }

        if (null !== $this->getParentKernelPath()) {
            $parentConfigDir = $this->getParentKernelPath().'/config';
            $loader->load($parentConfigDir.'/*'.self::CONFIG_EXTS, 'glob');

            if (file_exists($parentConfigDir.'/'.$this->environment)) {
                $loader->load($parentConfigDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
            }
        }

        $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');
        if (file_exists($confDir.'/'.$this->environment)) {
            $loader->load($confDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $globalConfDir = $this->getGlobalApplicationPath().'/routes';
        $confDir = $this->getApplicationDir().'/routes';

        $routes->import($globalConfDir.'/*'.self::CONFIG_EXTS, '/', 'glob');
        if (file_exists($globalConfDir.'/'.$this->environment)) {
            $routes->import($globalConfDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        }

        if (null !== $this->getParentKernelPath()) {
            $parentRoutesDir = $this->getParentKernelPath().'/routes';
            $routes->import($parentRoutesDir.'/*'.self::CONFIG_EXTS, '/', 'glob');
            if (file_exists($parentRoutesDir.'/'.$this->environment)) {
                $routes->import($parentRoutesDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
            }
        }

        $routes->import($confDir.'/*'.self::CONFIG_EXTS, '/', 'glob');
        if (file_exists($confDir.'/'.$this->environment)) {
            $routes->import($confDir.'/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        }
    }

    protected function getApplicationDir(): string
    {
        if (null === $this->applicationDir) {
            $reflector = new \ReflectionClass(\get_class($this));
            $this->applicationDir = \dirname($reflector->getFileName());
        }

        return $this->applicationDir;
    }

    public function getProjectDir(): string
    {
        if (null === $this->projectDir) {
            $reflector = new \ReflectionClass(\get_class($this));
            $this->projectDir = \dirname($reflector->getFileName()).'/../';
        }

        return $this->projectDir;
    }

    protected function getGlobalApplicationPath(): string
    {
        return $this->getProjectDir().'/application_global/';
    }

    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();

        $parameters['kernel.app_type'] = $this->getKernelType();
        $parameters['kernel.app'] = $this->getAppName();
        $parameters['kernel.parent_app'] = $this->getParentKernelName();

        return $parameters;
    }

    abstract protected function getKernelType(): string;

    protected function getAppName(): string
    {
        return mb_strtolower(str_replace(['App\\', 'Kernel'], '', \get_class($this)));
    }

    protected function getParentKernelPath(): ?string
    {
        return null;
    }

    protected function getParentKernelName(): ?string
    {
        return null;
    }
}

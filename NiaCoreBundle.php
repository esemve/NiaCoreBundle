<?php

declare(strict_types=1);

namespace Nia\CoreBundle;

use Nia\CoreBundle\Symfony\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NiaCoreBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $loader = $this->getYamlLoader(__DIR__, $container);

        $loader->load('config.yaml');
        $loader->load('services.yaml');
        $loader->load('events.yaml');
        $loader->load('commands.yaml');
        $loader->load('forms.yaml');
        $loader->load('twig_services.yaml');

        $this->loadEnvs(__DIR__, $container);
    }
}

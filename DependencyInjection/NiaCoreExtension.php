<?php

declare(strict_types=1);

namespace Nia\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NiaCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->loadEntityFactoryMapping($config, $container);
        $this->loadEnumOverride($config, $container);
        $this->loadManagerMapping($config, $container);
        $this->loadNotLoggableEntities($config, $container);
        $this->loadAvailableRoles($config, $container);
        $this->loadDisabledFilters($config, $container);
        $this->loadTranslationKeys($config, $container);
        $this->loadPortalLocales($config, $container);
        $this->loadEmailSettings($config, $container);
        $this->loadJavascriptTranslationKeys($config, $container);
        $this->loadContextConfig($config, $container);
        $this->loadCache($config, $container);
    }

    protected function loadEntityFactoryMapping(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.entity_override', $config['entity_override'] ?? []);
    }

    protected function loadEnumOverride(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.enum_override', $config['enum_override'] ?? []);
    }

    protected function loadContextConfig(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.service_context.config', $config['context'] ?? []);
    }

    protected function loadManagerMapping(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.manager_mapping', $config['manager_mapping'] ?? []);
    }

    protected function loadDisabledFilters(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.disabled_filters', $config['disabled_filters'] ?? []);
    }

    protected function loadPortalLocales(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.locales', $config['available_portal_locales']);
    }

    protected function loadJavascriptTranslationKeys(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.javascript_admin_translations', $config['javascript_admin_translations'] ?? []);
        $container->setParameter('nia.core.javascript_portal_translations', $config['javascript_portal_translations'] ?? []);
    }

    protected function loadEmailSettings(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('nia.core.mail_sending.default_sender_address', $config['mail_sending']['default_sender_address']);
        $container->setParameter('nia.core.mail_sending.default_sender_name', $config['mail_sending']['default_sender_name']);
    }

    private function loadAvailableRoles(array $config, ContainerBuilder $container)
    {
        if ($config['available_roles']) {
            $container->setParameter('nia.core.available_roles', $config['available_roles'] ?? []);
        }
    }

    private function loadTranslationKeys(array $config, ContainerBuilder $container)
    {
        if ($config['translation_keys']) {
            $container->setParameter('nia.core.translation_keys', $config['translation_keys'] ?? []);
        }
    }

    private function loadCache(array $config, ContainerBuilder $container)
    {
        if (!isset($config['cache'])) {
            $config['cache'] = [
                'type' => 'array',
                'prefix' => md5(__DIR__),
                'connection' => '',
            ];
        }

        $container->setParameter('nia.core.cache.type', $config['cache']['type'] ?? 'array');
        $container->setParameter('nia.core.cache.connection', $config['cache']['connection'] ?? '');
        $container->setParameter('nia.core.cache.prefix', $config['cache']['prefix'] ?? md5(__DIR__));
    }

    private function loadNotLoggableEntities(array $config, ContainerBuilder $container)
    {
        if ($config['not_loggable_entities']) {
            $container->setParameter('nia.core.log.not_loggable_entities', $config['not_loggable_entities'] ?? []);
        }
    }
}

<?php

declare(strict_types=1);

namespace Nia\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('nia_core');
        $rootNode = $treeBuilder->getRootNode();

        $this->buildEntityOverride($rootNode);
        $this->buildManagerMapping($rootNode);
        $this->buildDisabledFilters($rootNode);
        $this->buildTranslationKeys($rootNode);
        $this->buildCache($rootNode);
        $this->buildNotLoggable($rootNode);
        $this->buildPortalLocales($rootNode);
        $this->buildEnumOverride($rootNode);
        $this->buildAvailableRoles($rootNode);
        $this->buildEmailSettings($rootNode);
        $this->buildJavascriptTranslations($rootNode);
        $this->buildServiceContextConfig($rootNode);

        return $treeBuilder;
    }

    protected function buildServiceContextConfig(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('context')
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('roles')
                        ->prototype('scalar')
                    ->end()
                ->end()
            ->end();
    }

    protected function buildEntityOverride(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('entity_override')
            ->useAttributeAsKey('name')
            ->prototype('scalar')
            ->end();
    }

    protected function buildEnumOverride(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('enum_override')
            ->useAttributeAsKey('name')
            ->prototype('scalar')
            ->end();
    }

    protected function buildPortalLocales(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('available_portal_locales')
                ->prototype('scalar')
            ->end();
    }

    protected function buildManagerMapping(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('manager_mapping')
            ->useAttributeAsKey('name')
            ->prototype('scalar')
            ->end();
    }

    protected function buildDisabledFilters(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('disabled_filters')
            ->info('Entity - filter disabled')
            ->useAttributeAsKey('name')
            ->prototype('scalar')
            ->end();
    }

    private function buildAvailableRoles(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('available_roles')
            ->prototype('scalar')
            ->info('List of available roles')
            ->end();
    }

    private function buildTranslationKeys(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('translation_keys')
            ->prototype('scalar')
            ->info('Add plus translation keys')
            ->end();
    }

    private function buildJavascriptTranslations(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('javascript_admin_translations')
            ->prototype('scalar')
            ->info('Translations in admin to js file')
            ->end();

        $rootNode
            ->children()
            ->arrayNode('javascript_portal_translations')
            ->prototype('scalar')
            ->info('Translations in portal to js file')
            ->end();
    }

    private function buildEmailSettings(NodeDefinition $rootNode): void
    {
        $rootNode
        ->addDefaultsIfNotSet()
        ->children()
            ->arrayNode('mail_sending')
                ->addDefaultsIfNotSet()
                ->children()
                        ->scalarNode('default_sender_address')
                            ->info('Default e-mail sender address')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('default_sender_name')
                            ->info('Default e-mail sender name')
                            ->cannotBeEmpty()
                        ->end()
                ->end()
            ->end()
        ->end();
    }

    private function buildCache(NodeDefinition $rootNode): void
    {
        $rootNode
        ->addDefaultsIfNotSet()
        ->children()
            ->arrayNode('cache')
                ->addDefaultsIfNotSet()
                ->children()
                        ->scalarNode('type')
                            ->info('Cache type (redis / array)')
                        ->end()
                        ->scalarNode('connection')
                            ->info('Empty if nothing')
                        ->end()
                        ->scalarNode('prefix')
                            ->info('Keys prefix for this site')
                        ->end()
                ->end()
            ->end()
        ->end();
    }

    protected function buildNotLoggable(NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('not_loggable_entities')
            ->prototype('scalar')
            ->end();
    }
}

<?php
namespace M6Web\Bundle\CassandraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * http://symfony.com/fr/doc/current/components/config/definition.html
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6web_cassandra');

        $rootNode
            ->children()
                ->booleanNode('dispatch_events')->defaultValue(true)->end()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('id', false)
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return (array_key_exists('load_balancing', $v) &&   $v['load_balancing'] === 'dc-aware-round-robin') && !array_key_exists('dc_options', $v);
                            })
                            ->thenInvalid('"dc-aware-round-robin" load balancing option require a "dc_options" entry in your configuration')
                        ->end()
                        ->children()
                            ->booleanNode('persistent_sessions')->defaultValue(true)->end()
                            ->scalarNode('keyspace')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('load_balancing')
                                ->defaultValue('round-robin')
                                ->validate()
                                    ->ifNotInArray(['round-robin', 'dc-aware-round-robin'])
                                    ->thenInvalid('Invalid load balancing value "%s"')
                                ->end()
                            ->end()
                            ->arrayNode('dc_options')
                                ->children()
                                    ->scalarNode('local_dc_name')->isRequired()->cannotBeEmpty()->end()
                                    ->integerNode('host_per_remote_dc')->isRequired()->cannotBeEmpty()->end()
                                    ->booleanNode('remote_dc_for_local_consistency')->isRequired()->end()
                                ->end()
                            ->end()
                            ->scalarNode('default_consistency')
                                ->defaultValue('one')
                                ->validate()
                                    ->ifNotInArray(['one', 'any', 'two', 'three', 'quorum', 'all', 'local_quorum', 'each_quorum', 'serial', 'local_serial', 'local_one'])
                                    ->thenInvalid('Invalid consistency value "%s"')
                                ->end()
                            ->end()
                            ->integerNode('default_pagesize')->deFaultValue(10000)->end()
                            ->arrayNode('contact_endpoints')->isRequired()->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                            ->integerNode('port_endpoint')->defaultValue(9042)->end()
                            ->booleanNode('token_aware_routing')->defaultValue(true)->end()
                            ->arrayNode('credentials')
                                ->children()
                                    ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->booleanNode('ssl')->defaultValue(false)->end()
                            ->integerNode('default_timeout')->cannotBeEmpty()->end()
                            ->arrayNode('timeout')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->integerNode('connect')->isRequired()->defaultValue(5)->cannotBeEmpty()->end()
                                    ->integerNode('request')->isRequired()->defaultValue(5)->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->arrayNode('retries')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->integerNode('sync_requests')->defaultValue(0)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

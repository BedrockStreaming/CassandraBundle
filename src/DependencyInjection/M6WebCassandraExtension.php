<?php
namespace M6Web\Bundle\CassandraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebCassandraExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //$container->setParameter('m6web_cassandra', $config);
        foreach ($config['clients'] as $clientId => $clientConfig) {
            $clientConfig['dispatch_events'] = $config['dispatch_events'];
            $this->loadClient($container, $clientId, $clientConfig);
        }

    }

    protected function loadClient(ContainerBuilder $container, $clientId, array $config)
    {
        $class = 'M6Web\Bundle\CassandraBundle\Cassandra\Client';
        $definition = new Definition($class);
        $definition->addArgument($config);
        $definition->setConfigurator(['M6Web\Bundle\CassandraBundle\Cassandra\Configurator', 'buildCluster']);

        if ($config['dispatch_events']) {
            $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        }

        $container->setDefinition('m6web_cassandra.client.'.$clientId, $definition);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'm6web_cassandra';
    }
}

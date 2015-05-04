<?php
namespace M6Web\Bundle\CassandraBundle\Tests\Units\DependencyInjection;

use mageekguy\atoum\test;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use M6Web\Bundle\CassandraBundle\DependencyInjection\M6WebCassandraExtension as TestedClass;

class M6WebCassandraExtension extends test
{
    public function testDefaultConfig()
    {
        $container = $this->getContainerForConfiguation('default-config');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(10)
                ->hasKeys(['keyspace', 'contact_endpoints', 'load_balancing', 'default_consistency', 'default_pagesize', 'port_endpoint', 'token_aware_routing', 'ssl', 'timeout'])
                ->notHasKeys(['default_timeout'])
            ->boolean($arguments['dispatch_events'])
                ->isTrue()
            ->string($arguments['keyspace'])
                ->isEqualTo('test')
            ->array($endpoints = $arguments['contact_endpoints'])
                ->hasSize(3)
                ->string($endpoints[0])
                    ->isEqualTo('127.0.0.1')
                ->string($endpoints[1])
                    ->isEqualTo('127.0.0.2')
                ->string($endpoints[2])
                    ->isEqualTo('127.0.0.3')
            ->string($arguments['load_balancing'])
                ->isEqualTo('round-robin')
            ->string($arguments['default_consistency'])
                ->isEqualTo('one')
            ->integer($arguments['default_pagesize'])
                ->isEqualTo(10000)
            ->integer($arguments['port_endpoint'])
                ->isEqualTo(9042)
            ->boolean($arguments['token_aware_routing'])
                ->isTrue()
            ->boolean($arguments['ssl'])
                ->isFalse()
            ->array($timeouts = $arguments['timeout'])
                ->hasSize(2)
                ->hasKeys(['connect', 'request'])
                ->integer($timeouts['connect'])
                    ->isEqualTo(5)
                ->integer($timeouts['request'])
                    ->isEqualTo(5)
        ;
    }

    public function testOverrideConfig()
    {
        $container = $this->getContainerForConfiguation('override-config');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(13)
                ->hasKeys(['keyspace', 'contact_endpoints', 'load_balancing', 'default_consistency', 'default_pagesize', 'port_endpoint', 'token_aware_routing', 'ssl', 'timeout', 'credentials', 'default_timeout', 'dc_options'])
            ->boolean($arguments['dispatch_events'])
                ->isFalse()
            ->string($arguments['keyspace'])
                ->isEqualTo('test')
            ->array($endpoints = $arguments['contact_endpoints'])
                ->hasSize(3)
                ->string($endpoints[0])
                    ->isEqualTo('127.0.0.1')
                ->string($endpoints[1])
                    ->isEqualTo('127.0.0.2')
                ->string($endpoints[2])
                    ->isEqualTo('127.0.0.3')
            ->string($arguments['load_balancing'])
                ->isEqualTo('dc-aware-round-robin')
            ->array($dcOptions = $arguments['dc_options'])
                ->hasSize(3)
                ->hasKeys(['local_dc_name', 'host_per_remote_dc', 'remote_dc_for_local_consistency'])
                ->string($dcOptions['local_dc_name'])
                    ->isEqualTo('testdc')
                ->integer($dcOptions['host_per_remote_dc'])
                    ->isEqualTo(3)
                ->boolean($dcOptions['remote_dc_for_local_consistency'])
                    ->isFalse()
            ->string($arguments['default_consistency'])
                ->isEqualTo('two')
            ->integer($arguments['default_pagesize'])
                ->isEqualTo(1000)
            ->integer($arguments['port_endpoint'])
                ->isEqualTo(8906)
            ->boolean($arguments['token_aware_routing'])
                ->isFalse()
            ->boolean($arguments['ssl'])
                ->isTrue()
            ->integer($arguments['default_timeout'])
                ->isEqualTo(5)
            ->array($timeouts = $arguments['timeout'])
                ->hasSize(2)
                ->hasKeys(['connect', 'request'])
                ->integer($timeouts['connect'])
                    ->isEqualTo(15)
                ->integer($timeouts['request'])
                    ->isEqualTo(15)
            ->array($credentials = $arguments['credentials'])
                ->hasSize(2)
                ->hasKeys(['username', 'password'])
                ->string($credentials['username'])
                    ->isEqualTo('username')
                ->string($credentials['password'])
                    ->isEqualTo('password')
        ;
    }

    public function testMulticlientsConfig()
    {
        $container = $this->getContainerForConfiguation('multiclients');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(10)
                ->hasKeys(['keyspace', 'contact_endpoints', 'load_balancing', 'default_consistency', 'default_pagesize', 'port_endpoint', 'token_aware_routing', 'ssl', 'timeout'])
            ->boolean($arguments['dispatch_events'])
                ->isTrue()
            ->string($arguments['keyspace'])
                ->isEqualTo('test')
            ->array($endpoints = $arguments['contact_endpoints'])
                ->hasSize(3)
                ->string($endpoints[0])
                    ->isEqualTo('127.0.0.1')
                ->string($endpoints[1])
                    ->isEqualTo('127.0.0.2')
                ->string($endpoints[2])
                    ->isEqualTo('127.0.0.3')
            ->boolean($container->has('m6web_cassandra.client.client_test2'))
                ->isTrue()
            ->array($arguments2 = $container->getDefinition('m6web_cassandra.client.client_test2')->getArgument(0))
                ->hasSize(12)
                ->hasKeys(['keyspace', 'contact_endpoints', 'load_balancing', 'default_consistency', 'default_pagesize', 'port_endpoint', 'token_aware_routing', 'ssl', 'timeout', 'credentials', 'dc_options'])
            ->boolean($arguments['dispatch_events'])
                ->isTrue()
            ->string($arguments2['keyspace'])
                ->isEqualTo('test2')
            ->array($endpoints2 = $arguments2['contact_endpoints'])
                ->hasSize(2)
                ->string($endpoints2[0])
                    ->isEqualTo('127.0.0.4')
                ->string($endpoints2[1])
                    ->isEqualTo('127.0.0.5')
            ->array($credentials = $arguments2['credentials'])
                ->hasSize(2)
                ->hasKeys(['username', 'password'])
                ->string($credentials['username'])
                    ->isEqualTo('usertest')
                ->string($credentials['password'])
                    ->isEqualTo('passwdtest')
        ;

    }

    /**
     * @dataProvider unexpectedConfigValueDataProvider
     */
    public function testUnexpectedValueConfig($configs)
    {
        $parameterBag = new ParameterBag(array('kernel.debug' => true));
        $container = new ContainerBuilder($parameterBag);

        $this->if($extension = new TestedClass())
            ->exception(function() use($extension, $configs, $container) {
                $extension->load($configs, $container);
            })
            ->isInstanceOf('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

    }

    public function testInvalidConfig()
    {
        // no option for dc aware load balancing
        $configs = [[
            'clients' => [
                'client_test' => [
                    'keyspace' => 'test'
                    , 'contact_endpoints' => ['127.0.0.1']
                    , 'load_balancing' => 'dc-aware-round-robin'
                ]
            ]
        ]];

        $parameterBag = new ParameterBag(array('kernel.debug' => true));
        $container = new ContainerBuilder($parameterBag);

        $this->if($extension = new TestedClass())
            ->exception(function() use($extension, $configs, $container) {
                $extension->load($configs, $container);
            })
            ->isInstanceOf('\InvalidArgumentException');
    }

    public function testConfigurator()
    {
        $container = $this->getContainerForConfiguation('default-config');
        $container->compile();

        $this
            ->object($client = $container->get('m6web_cassandra.client.client_test'))
                ->isInstanceOf('M6Web\Bundle\CassandraBundle\Cassandra\Client')
            ->object($client->getCluster())
                ->isInstanceOf('Cassandra\DefaultCluster');
    }

    protected function unexpectedConfigValueDataProvider()
    {
        return [
            // bad load balancing
            [[[
                'clients' => [
                    'client_test' => [
                        'keyspace' => 'test'
                        , 'contact_endpoints' => ['127.0.0.1']
                        , 'load_balancing' => 'invalid'
                    ]
                ]
            ]]],
            // bad consistency
            [[[
                'clients' => [
                    'client_test' => [
                        'keyspace' => 'test'
                        , 'contact_endpoints' => ['127.0.0.1']
                        , 'default_consistency' => 'invalid'
                    ]
                ]
            ]]]
        ];
    }

    protected function getContainerForConfiguation($fixtureName)
    {
        $extension = new TestedClass();

        $parameterBag = new ParameterBag(array('kernel.debug' => true));
        $container = new ContainerBuilder($parameterBag);
        $container->set('event_dispatcher', new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface());
        $container->registerExtension($extension);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Fixtures/'));
        $loader->load($fixtureName.'.yml');

        return $container;
    }
}

<?php
namespace M6Web\Bundle\CassandraBundle\Tests\Units\DependencyInjection;

use mageekguy\atoum\test;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use M6Web\Bundle\CassandraBundle\DependencyInjection\M6WebCassandraExtension as TestedClass;

/**
 * Class M6WebCassandraExtension
 * @package M6Web\Bundle\CassandraBundle\Tests\Units\DependencyInjection
 */
class M6WebCassandraExtension extends test
{
    /**
     * @return void
     */
    public function testDefaultConfig()
    {
        $container = $this->getContainerForConfiguration('default-config');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(16)
                ->hasKeys($this->getDefaultConfigKeys())
                ->notHasKeys(['default_timeout'])
            ->boolean($arguments['dispatch_events'])
                ->isTrue()
            ->boolean($arguments['persistent_sessions'])
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
            ->array($arguments['dc_whitelist'])
                ->hasSize(0)
            ->array($arguments['dc_blacklist'])
                ->hasSize(0)
            ->array($arguments['hosts_whitelist'])
                ->hasSize(0)
            ->array($arguments['hosts_blacklist'])
                ->hasSize(0)
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
            ->array($retries = $arguments['retries'])
                ->hasSize(1)
                ->integer($retries['sync_requests'])
                    ->isEqualTo(0)
        ;
    }

    /**
     * @return void
     */
    public function testShouldGetADefaultNullKeyspaceWhenNoKeyspaceGiven()
    {
        $container = $this->getContainerForConfiguration('default-config-without-keyspace');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(16)
                ->hasKeys($this->getDefaultConfigKeys())
                ->notHasKeys(['default_timeout'])
            ->variable($arguments['keyspace'])
                ->isNull()
        ;
    }

    /**
     * @return void
     */
    public function testOverrideConfig()
    {
        $container = $this->getContainerForConfiguration('override-config');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(19)
                ->hasKeys($this->getDefaultConfigKeys(['credentials', 'default_timeout', 'dc_options']))
            ->boolean($arguments['dispatch_events'])
                ->isFalse()
            ->boolean($arguments['persistent_sessions'])
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
            ->array($list = $arguments['dc_whitelist'])
                ->hasSize(1)
                ->string($list[0])
                    ->isEqualTo('testdc')
            ->array($list = $arguments['dc_blacklist'])
                ->hasSize(1)
                ->string($list[0])
                    ->isEqualTo('blacklisted_testdc')
            ->array($list = $arguments['hosts_whitelist'])
                ->hasSize(1)
                ->string($list[0])
                    ->isEqualTo('172.0.0.1')
            ->array($list = $arguments['hosts_blacklist'])
                ->hasSize(0)
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
            ->array($retries = $arguments['retries'])
                ->hasSize(1)
                ->integer($retries['sync_requests'])
                    ->isEqualTo(1)
        ;
    }

    /**
     * @return void
     */
    public function testOverrideDefaultEndPointsConfig()
    {
        $container = $this->getContainerForConfiguration('override-config-with-import');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
            ->array($endpoints = $arguments['contact_endpoints'])
                ->hasSize(3)
                ->string($endpoints[0])
                    ->isEqualTo('127.0.0.4')
                ->string($endpoints[1])
                    ->isEqualTo('127.0.0.5')
                ->string($endpoints[2])
                    ->isEqualTo('127.0.0.6')
            ->array($arguments['dc_whitelist'])
                ->hasSize(0)
            ->array($arguments['dc_blacklist'])
                ->hasSize(0)
            ->array($arguments['hosts_whitelist'])
                ->hasSize(0)
            ->array($arguments['hosts_blacklist'])
                ->hasSize(0)
            ->variable($arguments['default_pagesize'])
                ->isNull()
        ;
    }

    /**
     * @return void
     */
    public function testMulticlientsConfig()
    {
        $container = $this->getContainerForConfiguration('multiclients');
        $container->compile();

        $this
            ->boolean($container->has('m6web_cassandra.client.client_test'))
                ->isTrue()
            ->array($arguments = $container->getDefinition('m6web_cassandra.client.client_test')->getArgument(0))
                ->hasSize(16)
                ->hasKeys($this->getDefaultConfigKeys())
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
            ->array($arguments['dc_whitelist'])
                ->hasSize(0)
            ->array($arguments['dc_blacklist'])
                ->hasSize(0)
            ->array($arguments['hosts_whitelist'])
                ->hasSize(0)
            ->array($arguments['hosts_blacklist'])
                ->hasSize(0)
            ->boolean($container->has('m6web_cassandra.client.client_test2'))
                ->isTrue()
            ->array($arguments2 = $container->getDefinition('m6web_cassandra.client.client_test2')->getArgument(0))
                ->hasSize(18)
                ->hasKeys($this->getDefaultConfigKeys(['credentials', 'dc_options']))
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
            ->array($arguments['dc_whitelist'])
                ->hasSize(0)
            ->array($arguments['dc_blacklist'])
                ->hasSize(0)
            ->array($arguments['hosts_whitelist'])
                ->hasSize(0)
            ->array($arguments['hosts_blacklist'])
                ->hasSize(0)
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
     * @param array $configs
     * @return void
     *
     * @dataProvider unexpectedConfigValueDataProvider
     */
    public function testUnexpectedValueConfig($configs)
    {
        $parameterBag = new ParameterBag(array('kernel.debug' => true));
        $container = new ContainerBuilder($parameterBag);

        $this->if($extension = new TestedClass())
            ->exception(function () use ($extension, $configs, $container) {
                $extension->load($configs, $container);
            })
            ->isInstanceOf('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
    }

    /**
     * @return void
     */
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
            ->exception(function () use ($extension, $configs, $container) {
                $extension->load($configs, $container);
            })
            ->isInstanceOf('\InvalidArgumentException');
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testConfigurator()
    {
        $container = $this->getContainerForConfiguration('default-config');
        $container->compile();

        $this
            ->object($client = $container->get('m6web_cassandra.client.client_test'))
                ->isInstanceOf('M6Web\Bundle\CassandraBundle\Cassandra\Client')
            ->object($client->getCluster())
                ->isInstanceOf('Cassandra\DefaultCluster');
    }

    /**
     * @return array
     */
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

    /**
     * @param $fixtureName
     * @return ContainerBuilder
     */
    protected function getContainerForConfiguration($fixtureName)
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

    /**
     * @param array $keySup
     * @return array
     */
    protected function getDefaultConfigKeys(array $keySup = [])
    {
            return array_merge(
                [
                'persistent_sessions',
                'keyspace',
                'contact_endpoints',
                'dc_whitelist',
                'dc_blacklist',
                'hosts_whitelist',
                'hosts_blacklist',
                'load_balancing',
                'default_consistency',
                'default_pagesize',
                'port_endpoint',
                'token_aware_routing',
                'ssl',
                'timeout',
                'retries'
                ],
                $keySup
            );
    }
}

<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

use Cassandra\Cluster\Builder;
use Cassandra\SSLOptions\Builder as SSLOptionsBuilder;

/**
 * Class Configurator
 *
 * Configure cluster for cassandra client
 */
class Configurator
{
    /**
     * Configure given client
     *
     * @param Client $client
     */
    public static function buildCluster(Client $client)
    {
        $config = $client->getConfig();

        $consistency = constant('\Cassandra::CONSISTENCY_'.strtoupper($config['default_consistency']));

        $cluster = new Builder();
        $cluster
            ->withDefaultConsistency($consistency)
            ->withDefaultPageSize($config['default_pagesize'])
            ->withContactPoints(implode(',', $config['contact_endpoints']))
            ->withPort($config['port_endpoint'])
            ->withTokenAwareRouting($config['token_aware_routing'])
            ->withConnectTimeout($config['timeout']['connect'])
            ->withRequestTimeout($config['timeout']['request']);

        if (isset($config['ssl']) && $config['ssl'] === true) {
            $ssl = new SSLOptionsBuilder();
            $sslOption = $ssl->withVerifyFlags(\Cassandra::VERIFY_NONE)->build();
            $cluster->withSSL($sslOption);
        }

        if (array_key_exists('default_timeout', $config)) {
            $cluster->withDefaultTimeout($config['default_timeout']);
        }

        if ($config['load_balancing'] == 'round-robin') {
            $cluster->withRoundRobinLoadBalancingPolicy();
        } else {
            $dcOption = $config['dc_options'];
            $cluster->withDatacenterAwareRoundRobinLoadBalancingPolicy($dcOption['local_dc_name'], $dcOption['host_per_remote_dc'], $dcOption['remote_dc_for_local_consistency']);
        }

        if (array_key_exists('credentials', $config)) {
            $cluster->withCredentials($config['credentials']['username'], $config['credentials']['password']);
        }

        $client->setCluster($cluster->build());
    }
}

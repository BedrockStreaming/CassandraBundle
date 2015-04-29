<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

use Cassandra\Cluster\Builder;
use Cassandra\ExecutionOptions;
use Cassandra\PreparedStatement;
use Cassandra\Session;
use Cassandra\Statement;
use Cassandra\DefaultSession;
use Cassandra\DefaultCluster;
use Cassandra\SSLOptions\Builder as SSLOptionsBuilder;

/**
 * Class Client
 *
 * Client to connect and query a cassandra cluster
 */
class Client implements Session
{
    /**
     * @var DefaultCluster
     */
    protected $cluster;

    /**
     * @var DefaultSession
     */
    protected $session;

    /**
     * Construct the client
     *
     * Initialize cluster and aggregate the session
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->buildCluster($config);

        $session = $this->cluster->connect($config['keyspace']);

        $this->setSession($session);
    }

    /**
     * Set session
     *
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Executes a given statement and returns a result
     *
     * @param Statement        $statement statement to be executed
     * @param ExecutionOptions $options   execution options
     *
     * @throws \Cassandra\Exception
     *
     * @return \Cassandra\Future  execution result
     */
    public function execute(Statement $statement, ExecutionOptions $options = null)
    {
        return $this->session->execute($statement, $options);
    }

    /**
     * Executes a given statement and returns a future result
     *
     * Note that this method ignores ExecutionOptions::$timeout option, you can
     * provide one to Future::get() instead.
     *
     * @param Statement        $statement statement to be executed
     * @param ExecutionOptions $options   execution options
     *
     * @return \Cassandra\Future     future result
     */
    public function executeAsync(Statement $statement, ExecutionOptions $options = null)
    {
        return $this->session->executeAsync($statement, $options);
    }

    /**
     * Creates a prepared statement from a given CQL string
     *
     * Note that this method only uses the ExecutionOptions::$timeout option,
     * all other options will be ignored.
     *
     * @param string           $cql     CQL statement string
     * @param ExecutionOptions $options execution options
     *
     * @throws \Cassandra\Exception
     *
     * @return PreparedStatement  prepared statement
     */
    public function prepare($cql, ExecutionOptions $options = null)
    {
        return $this->session->prepare($cql, $options);
    }

    /**
     * Asynchronously prepares a statement and returns a future prepared statement
     *
     * Note that all options passed to this method will be ignored.
     *
     * @param string           $cql     CQL string to be prepared
     * @param ExecutionOptions $options preparation options
     *
     * @return \Cassandra\Future  statement
     */
    public function prepareAsync($cql, ExecutionOptions $options = null)
    {
        return $this->session->prepareAsync($cql, $options);
    }

    /**
     * Closes current session and all of its connections
     *
     * @param float|null $timeout Timeout to wait for closure in seconds
     *
     * @return void
     */
    public function close($timeout = null)
    {
        $this->session->close($timeout);
    }

    /**
     * Asynchronously closes current session once all pending requests have finished
     *
     * @return \Cassandra\Future  future
     */
    public function closeAsync()
    {
        $this->session->closeAsync();
    }

    /**
     * Build cassandra cluster
     *
     * @param array $config
     */
    protected function buildCluster(array $config)
    {
        $cluster = new Builder();
        $cluster ->withDefaultConsistency($this->getConsistency($config['default_consistency']))
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
            $cluster->withRoundRobinLoadBalancingPolicy(\Cassandra::LOAD_BALANCING_ROUND_ROBIN);
        } else {
            $dcOption = $config['dc_options'];
            $cluster->withDatacenterAwareRoundRobinLoadBalancingPolicy($dcOption['local_dc_name'], $dcOption['host_per_remote_dc'], $dcOption['remote_dc_for_local_consistency']);
        }

        if (array_key_exists('credentials', $config)) {
            $cluster->withCredentials($config['credentials']['username'], $config['credentials']['password']);
        }

        $this->cluster = $cluster->build();
    }

    /**
     * Return cassandra consistency value
     *
     * @param string $consistency
     *
     * @return mixed
     */
    protected function getConsistency($consistency)
    {
        return constant('\Cassandra::CONSISTENCY_'.strtoupper($consistency));
    }


}
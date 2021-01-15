<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

use Cassandra\Future;
use Cassandra\PreparedStatement;
use Cassandra\Session;
use Cassandra\Statement;
use Cassandra\DefaultSession;
use Cassandra\Cluster;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use M6Web\Bundle\CassandraBundle\EventDispatcher\CassandraEvent;

/**
 * Class Client
 *
 * Client to connect and query a cassandra cluster
 */
class Client implements Session
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Cluster
     */
    protected $cluster;

    /**
     * @var DefaultSession
     */
    protected $session;

    /**
     * @var string
     */
    protected $keyspace;

    /**
     * @var integer
     */
    protected $maxRetry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Construct the client
     *
     * Initialize cluster and aggregate the session
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->session = null;

        $this->keyspace = $config['keyspace'];
        $this->maxRetry = $config['retries']['sync_requests'];
    }

    /**
     * Set event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Set cluster
     *
     * @param Cluster $cluster
     */
    public function setCluster(Cluster $cluster)
    {
        $this->cluster = $cluster;
    }

    /**
     * @return Cluster
     */
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * Return client configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return keyspace to use with session
     *
     * @return string
     */
    public function getKeyspace()
    {
        return $this->keyspace;
    }

    /**
     * Return Cassandra session
     *
     * @return Session
     */
    public function getSession()
    {
        if (is_null($this->session)) {
            $this->session = $this->cluster->connect($this->getKeyspace());
        }

        return $this->session;
    }

    /**
     * Executes a given statement and returns a result
     *
     * @param Statement $statement statement to be executed
     * @param array     $options   execution options
     *
     * @throws \Cassandra\Exception
     *
     * @return \Cassandra\Rows execution result
     */
    public function execute($statement, $options = null)
    {
        return $this->send('execute', [$statement, $options]);
    }

    /**
     * Executes a given statement and returns a future result
     *
     * Note that this method ignores $options['timeout'] option, you can
     * provide one to Future::get() instead.
     *
     * @param Statement $statement statement to be executed
     * @param array     $options   execution options
     *
     * @return \Cassandra\Future     future result
     */
    public function executeAsync($statement, $options = null)
    {
        return $this->send('executeAsync', [$statement, $options]);
    }

    /**
     * Creates a prepared statement from a given CQL string
     *
     * Note that this method only uses the $options['timeout'] option,
     * all other options will be ignored.
     *
     * @param string $cql     CQL statement string
     * @param array  $options execution options
     *
     * @throws \Cassandra\Exception
     *
     * @return PreparedStatement  prepared statement
     */
    public function prepare($cql, $options = null)
    {
        return $this->send('prepare', [$cql, $options]);
    }

    /**
     * Asynchronously prepares a statement and returns a future prepared statement
     *
     * Note that all options passed to this method will be ignored.
     *
     * @param string $cql     CQL string to be prepared
     * @param array  $options preparation options
     *
     * @return \Cassandra\Future  statement
     */
    public function prepareAsync($cql, $options = null)
    {
        return $this->send('prepareAsync', [$cql, $options]);
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
        $this->getSession()->close($timeout);
        $this->resetSession();
    }

    /**
     * Asynchronously closes current session once all pending requests have finished
     *
     * @return \Cassandra\Future  future
     */
    public function closeAsync()
    {
        $this->getSession()->closeAsync();
        $this->resetSession();
    }

    /**
     * Returns current schema.
     *
     * NOTE: the returned Schema instance will not be updated as the actual
     *       schema changes, instead an updated instance should be requested by
     *       calling Session::schema() again.
     *
     * @return \Cassandra\Schema
     */
    public function schema()
    {
        return $this->getSession()->schema();
    }

    /**
     * @return array Performance/Diagnostic metrics.
     */
    public function metrics()
    {
        return $this->getSession()->metrics();
    }

    /**
     * Reset cassandra session
     */
    protected function resetSession()
    {
        $this->session = null;
    }

    /**
     * Initialize event
     *
     * @param string $command
     * @param array  $args
     *
     * @return CassandraEvent|null Return null if no eventDispatcher available
     */
    protected function prepareEvent($command, array $args)
    {
        if (is_null($this->eventDispatcher)) {
            return null;
        }

        $event = new CassandraEvent();
        $event->setCommand($command)
              ->setKeyspace($this->getKeyspace())
              ->setArguments($args)
              ->setExecutionStart();

        return $event;
    }

    /**
     * Prepare response to return
     *
     * @param mixed               $response
     * @param CassandraEvent|null $event
     *
     * @return mixed
     */
    protected function prepareResponse($response, CassandraEvent $event = null)
    {
        if (is_null($event)) {
            return $response;
        }

        if ($response instanceof Future) {
            return new FutureResponse($response, $event, $this->eventDispatcher);
        }

        $event->setExecutionStop();
        $this->eventDispatcher->dispatch($event, CassandraEvent::EVENT_NAME);

        return $response;
    }

    /**
     * Send command to cassandra session
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function send($command, array $arguments)
    {
        $event = $this->prepareEvent($command, $arguments);

        // The last arguments of call_user_func_array must not be null
        if (end($arguments) === null) {
            array_pop($arguments);
        }

        $retry = $this->maxRetry;
        while ($retry >= 0) {
            try {
                $return = call_user_func_array([$this->getSession(), $command], $arguments);

                // No exception, we can return the result
                $retry = -1;
            } catch (\Cassandra\Exception\RuntimeException $e) {
                if ($retry > 0) {
                    // Reset the current session to retry the command
                    $this->resetSession();
                    $retry--;
                } else {
                    // too many retries, rethrow the exception
                    throw $e;
                }
            }
        }

        return $this->prepareResponse($return, $event);
    }
}

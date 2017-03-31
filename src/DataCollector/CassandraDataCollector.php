<?php
namespace M6Web\Bundle\CassandraBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use M6Web\Bundle\CassandraBundle\EventDispatcher\CassandraEvent;

/**
 * Collect information about cassandra command
 */
class CassandraDataCollector extends DataCollector
{
    /**
     * Human readable values for consistency
     *
     * @var array
     */
    static protected $consistency = [
        \Cassandra::CONSISTENCY_ANY          => 'any',
        \Cassandra::CONSISTENCY_ONE          => 'one',
        \Cassandra::CONSISTENCY_TWO          => 'two',
        \Cassandra::CONSISTENCY_THREE        => 'three',
        \Cassandra::CONSISTENCY_QUORUM       => 'quorum',
        \Cassandra::CONSISTENCY_ALL          => 'all',
        \Cassandra::CONSISTENCY_LOCAL_QUORUM => 'local quorum',
        \Cassandra::CONSISTENCY_EACH_QUORUM  => 'each quorum',
        \Cassandra::CONSISTENCY_SERIAL       => 'serial',
        \Cassandra::CONSISTENCY_LOCAL_SERIAL => 'local serial',
        \Cassandra::CONSISTENCY_LOCAL_ONE    => 'local one'
    ];

    /**
     * Construct the data collector
     */
    public function __construct()
    {
        $this->data['cassandra'] = new \SplQueue();
    }

    /**
     * Collect the data
     * @param Request    $request   The request object
     * @param Response   $response  The response object
     * @param \Exception $exception An exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * Return the name of the collector
     *
     * @return string data collector name
     */
    public function getName()
    {
        return 'cassandra';
    }

    /**
     * Collect data for casandra command
     *
     * Listen for cassandra event
     *
     * @param CassandraEvent $event
     */
    public function onCassandraCommand(CassandraEvent $event)
    {
        $data = [
            'keyspace'      => $event->getKeyspace(),
            'command'       => $event->getCommand(),
            'argument'      => $this->getArguments($event),
            'options'       => $this->getOptions($event),
            'executionTime' => $event->getExecutionTime()
        ];

        $this->data['cassandra']->enqueue($data);
    }

    /**
     * Return cassandra command list
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->data['cassandra'];
    }

    /**
     * Return the total time spent by cassandra commands
     *
     * @return float
     */
    public function getTotalExecutionTime()
    {
        return array_reduce(iterator_to_array($this->getCommands()), function ($time, $value) {
            $time += $value['executionTime'];

            return $time;
        });
    }

    /**
     * Return average time spent by cassandra command
     *
     * @return float
     */
    public function getAvgExecutionTime()
    {
        $totalExecutionTime = $this->getTotalExecutionTime();

        return ($totalExecutionTime) ? ($totalExecutionTime / count($this->getCommands()) ) : 0;
    }

    /**
     * Get argument to display in datacollector panel
     *
     * @param CassandraEvent $event
     *
     * @return string
     */
    protected function getArguments(CassandraEvent $event)
    {
        $arguments = $event->getArguments();

        if (is_object($arguments[0])) {
            return 'Statement';
        }

        return $arguments[0];
    }

    /**
     * Return the cassandra options defined at runtime
     *
     * @param CassandraEvent $event
     *
     * @return array
     */
    protected function getOptions(CassandraEvent $event)
    {
        $options = $event->getArguments()[1];

        return [
            'consistency'       => self::getConsistency($options['consistency'] ?? ''),
            'serialConsistency' => self::getConsistency($options['serialConsistency'] ?? ''),
            'pageSize'          => $options['pageSize'] ?? '',
            'timeout'           => $options['timeout'] ?? '',
            'arguments'         => var_export($options['arguments'] ?? '', true)
        ];

    }

    /**
     * Get human readable value of consistency
     *
     * @param int $intval
     *
     * @return string|null
     */
    protected static function getConsistency($intval)
    {
        if (array_key_exists($intval, self::$consistency)) {
            return self::$consistency[$intval];
        }

        return null;
    }
}
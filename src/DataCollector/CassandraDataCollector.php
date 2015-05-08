<?php
namespace M6Web\Bundle\CassandraBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Collect information about cassandra command
 */
class CassandraDataCollector extends DataCollector
{
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
            'keyspace'         => $event->getKeyspace(),
            'command'          => $event->getCommand(),
            'argument'         => $this->getArgument($event),
            'executionOptions' => $this->getExecutionOption($event),
            'executionTime'    => $event->getExecutionTime()
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
    protected function getExecutionOptions(CassandraEvent $event)
    {
        $arguments = $event->getArguments();

        if (empty($arguments[1])) {
            return [
                'consistency'       => '',
                'serialConsistency' => '',
                'pageSize'          => '',
                'timeout'           => '',
                'arguments'         => ''
            ];
        }

        $options = $arguments[1];

        return [
            'consistency'       => $options->consistency,
            'serialConsistency' => $options->serialConsistency,
            'pageSize'          => $options->pageSize,
            'timeout'           => $options->timeout,
            'arguments'         => var_export($options->arguments, true)
        ];

    }
}
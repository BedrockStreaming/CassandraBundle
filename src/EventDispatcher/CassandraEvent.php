<?php
namespace M6Web\Bundle\CassandraBundle\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CassandraEvent
 */
class CassandraEvent extends Event
{
    const EVENT_NAME = 'm6web.cassandra';

    /**
     * Cassandra keyspace where event was dispatched
     *
     * @var string
     */
    protected $keyspace;

    /**
     * Cassandra's session command invoked
     *
     * @var string
     */
    protected $command;

    /**
     * Command arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Command execution time
     *
     * @var float
     */
    protected $executionStart;

    /**
     * @var float
     */
    protected $executionTime;

    /**
     * @param string $keyspace
     *
     * @return CassandraEvent
     */
    public function setKeyspace($keyspace)
    {
        $this->keyspace = $keyspace;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyspace()
    {
        return $this->keyspace;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     *
     * @return CassandraEvent
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     *
     * @return CassandraEvent
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return float
     */
    public function getExecutionStart()
    {
        return $this->executionStart;
    }

    /**
     * Set execution start of a cassandra request
     *
     * @return CassandraEvent
     */
    public function setExecutionStart()
    {
        $this->executionStart = microtime(true);

        return $this;
    }

    /**
     * Stop the execution of cassandra request
     * and set the request execution time
     *
     * @return CassandraEvent
     */
    public function setExecutionStop()
    {
        $this->executionTime = microtime(true) - $this->executionStart;

        return $this;
    }

    /**
     * @return float
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * Return execution time in milliseconds
     *
     * @return float
     */
    public function getTiming()
    {
        return $this->getExecutionTime() * 1000;
    }
}

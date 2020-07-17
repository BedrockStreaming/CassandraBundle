<?php
namespace M6Web\Bundle\CassandraBundle\EventDispatcher;

use Symfony\Contracts\EventDispatcher\ContractsEvent;
use Symfony\Component\EventDispatcher\Event;

/*
 * Since Symfony 4.3, Event should derive from the contract class
 * and not the class from the EventDispatcher component. In order to
 * maintain compatibility with Symfony < 4.3, it's necessary to check
 * for the existence of the Contracts class.
 *
 * Apparently, there are no more clever ways than duplicating the class
 * definition for both cases, see
 * https://github.com/symfony/symfony/blob/4.4/src/Symfony/Contracts/EventDispatcher/Event.php
 * for another example where it's done.
 */
if (class_exists(ContractsEvent::class)) {
    /**
     * Class CassandraEvent
     */
    class CassandraEvent extends ContractsEvent
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
} else {
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
}

<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

use Cassandra\Future;
use M6Web\Bundle\CassandraBundle\EventDispatcher\CassandraEvent;

/**
 * Class FutureResponse
 *
 * Handle future response for dispatching event when request is complete
 */
class FutureResponse implements Future
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var CassandraEvent
     */
    protected $event;

    /**
     * @var Futurer
     */
    protected $future;

    /**
     * @param Future          $future
     * @param CassandraEvent  $event
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Future $future, CassandraEvent $event, $eventDispatcher)
    {
        $this->future          = $future;
        $this->event           = $event;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Waits for a given future resource to resolve and throws errors if any.
     *
     * @param int|null $timeout
     *
     * @throws \Cassandra\Exception\InvalidArgumentException
     * @throws \Cassandra\Exception\TimeoutException
     *
     * @return mixed a value that the future has been resolved with
     */
    public function get($timeout = null)
    {
        $return = $this->future->get($timeout);

        $this->event->setExecutionStop();
        $this->eventDispatcher->dispatch(CassandraEvent::EVENT_NAME, $this->event);

        return $return;
    }
}
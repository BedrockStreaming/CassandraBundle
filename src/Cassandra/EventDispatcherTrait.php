<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use M6Web\Bundle\CassandraBundle\EventDispatcher\CassandraEvent;

/**
 * Trait EventDispatcherTrait
 *
 * Dispatch Cassandra events correctly for all versions of Symfony
 * and work around the breaking change in Symfony 4.3 that reversed
 * the order of arguments of the dispatch() method in interface
 * EventDispatcherInterface.
 */
trait EventDispatcherTrait
{
    /**
     * @var boolean
     */
    protected $eventDispatcherTakesEventNameAsFirstArgument;

    /**
     * Initialize the trait
     *
     * Identify whether the event dispatcher takes the event name as
     * first argument in the dispatch() method.
     */
    private function initializeEventDispatcherTrait()
    {
        $rfl = new \ReflectionMethod(EventDispatcherInterface::class, 'dispatch');
        $params = $rfl->getParameters();
        $this->eventDispatcherTakesEventNameAsFirstArgument = $params[0]->getName() === "eventName";
    }

    /**
     * Dispatch the event in the appropriate way by the event dispatcher
     *
     * In a breaking change in Symfony 4.3, the order of arguments of the dispatch()
     * method have changed, this method does the right thing depending on the
     * version of the Symfony EventDispatcher component used.
     *
     * @param CassandraEvent $event The event to dispatch
     * @param string $eventName The name of the event to dispatch
     */
    protected function dispatchEvent(CassandraEvent $event, string $eventName)
    {
        if (!isset($this->eventDispatcherTakesEventNameAsFirstArgument)) {
            $this->initializeEventDispatcherTrait();
        }

        if ($this->eventDispatcherTakesEventNameAsFirstArgument) {
            $this->eventDispatcher->dispatch($eventName, $event);
        } else {
            $this->eventDispatcher->dispatch($event, $eventName);
        }
    }
}

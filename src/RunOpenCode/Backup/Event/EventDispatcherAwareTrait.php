<?php
/*
 * This file is part of the Backup package, an RunOpenCode project.
 *
 * (c) 2015 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is fork of "kbond/php-backup", for full credits info, please
 * view CREDITS file that was distributed with this source code.
 */
namespace RunOpenCode\Backup\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventDispatcherAwareTrait
 *
 * Implementation of \RunOpenCode\Backup\Contract\EventDispatcherAwareInterface.
 *
 * @package RunOpenCode\Backup\Event
 */
trait EventDispatcherAwareTrait
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    /**
     * Get event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            throw new \LogicException('Event dispatcher was not set');
        }

        return $this->eventDispatcher;
    }
}
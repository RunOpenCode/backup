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
namespace RunOpenCode\Backup;

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Contract\ManagerInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Manager
 *
 * Backup manager.
 *
 * @package RunOpenCode\Backup
 */
final class Manager implements ManagerInterface
{
    /**
     * @var ProfileInterface[]
     */
    private $profiles;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, $profiles = array())
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->profiles = $profiles;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ProfileInterface $profile)
    {
        $this->profiles[$profile->getName()] = $profile;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return (isset($this->profiles[$name]));
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->profiles[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function execute($name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException(sprintf('Unknown profile: "%s".', $name));
        }

        $workflow = $this->get($name)->getWorkflow();

        $workflow->setLogger($this->logger);
        $workflow->setEventDispatcher($this->eventDispatcher);

        $workflow->execute($this->get($name));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->profiles);
    }
}

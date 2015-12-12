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
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\EmptySourceException;
use RunOpenCode\Backup\Log\LoggerAwareTrait;
use RunOpenCode\Backup\Workflow\Workflow;
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
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;

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

    public function __construct(EventDispatcherInterface $eventDispatcher = null, LoggerInterface $logger = null, $profiles = array())
    {
        $this->profiles = $profiles;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
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

        $profile = $this->get($name);

        $workflow = new Workflow($profile, $this->eventDispatcher, $this->logger);

        try {

            $workflow->execute();

        } catch (EmptySourceException $e) {

            $this->logger->info(sprintf('Backup for profile "%s" didn\'t yield any file for backup.', $profile->getName()));

        } catch (\Exception $e) {

            $this->eventDispatcher->dispatch(BackupEvents::ERROR, new BackupEvent($this, $profile));
            $this->logger->critical(sprintf('There has been an error while executing backup profile "%s".', $profile->getName()), array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ));

        } finally {

            try {

                $this->eventDispatcher->dispatch(BackupEvents::TERMINATE, new BackupEvent($profile));
                $this->logger->info(sprintf('Backup for profile "%s" successfully terminated.', $profile->getName()));

            } catch (\Exception $e) {

                $this->logger->alert(sprintf('Could not terminate backup process for profile "%s".', $profile->getName()));

            }

        }

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

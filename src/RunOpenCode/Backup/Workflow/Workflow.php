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
namespace RunOpenCode\Backup\Workflow;

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\WorkflowActivityInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Workflow
 *
 * Workflow is entry point of backup workflow that executes workflow activities in given sequence.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class Workflow
{
    /**
     * @var array
     */
    private $activities;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ProfileInterface $profile, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, array $activities = array())
    {
        $this->profile = $profile;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->activities = count($activities) ? $activities : array(new Fetch(), new Process(), new Name(), new PreRotate(), new Push(), new PostRotate());
    }

    public function execute()
    {
        $backup = new Backup($this->profile->getName());

        $this->logger->info(sprintf('About to execute backup for profile: "%s".', $this->profile->getName()));
        $this->eventDispatcher->dispatch(BackupEvents::BEGIN, new BackupEvent($this, $this->profile, $backup));

        /**
         * @var WorkflowActivityInterface $activity
         */
        foreach ($this->activities as $activity) {

            $activity
                ->setBackup($backup)
                ->setProfile($this->profile);

            if ($activity instanceof LoggerAwareInterface) {
                $activity->setLogger($this->logger);
            }

            if ($activity instanceof EventDispatcherAwareInterface) {
                $activity->setEventDispatcher($this->eventDispatcher);
            }

            try {

                $activity->execute();

            } catch (\Exception $e) {

                $this->eventDispatcher->dispatch(BackupEvents::ERROR, new BackupEvent($this, $this->profile, $backup, $activity));

                throw $e;
            }
        }
    }
}

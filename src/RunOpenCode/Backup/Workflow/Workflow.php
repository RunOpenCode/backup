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
        $this->activities = array_merge(array(
            Fetch::class,
            Process::class,
            Name::class,
            PreRotate::class,
            Push::class,
            PostRotate::class
        ), $activities);
    }

    public function execute()
    {
        $backup = new Backup($this->profile->getName());

        $this->logger->info(sprintf('About to execute backup for profile: "%s".', $this->profile->getName()));
        $this->eventDispatcher->dispatch(BackupEvent::BEGIN, new BackupEvent($this->profile, $backup, null));

        foreach ($this->activities as $activityClass) {
            /**
             * @var WorkflowActivityInterface $activity
             */
            $activity = new $activityClass();

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

                $this->eventDispatcher->dispatch(BackupEvent::ERROR, new BackupEvent($this->profile, $backup, $activity));

                throw $e;
            }

        }
    }
}
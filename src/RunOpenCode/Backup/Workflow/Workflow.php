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
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\WorkflowActivityInterface;
use RunOpenCode\Backup\Contract\WorkflowInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\EmptySourceException;
use RunOpenCode\Backup\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Workflow
 *
 * Workflow is entry point of backup workflow that executes workflow activities in given sequence.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class Workflow implements WorkflowInterface
{
    use EventDispatcherAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var WorkflowActivityInterface[]
     */
    private $activities;

    public function __construct(array $activities)
    {
        $this->activities = $activities;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProfileInterface $profile)
    {
        if (empty($this->eventDispatcher) || empty($this->logger)) {
            throw new \LogicException('Workflow can not be executed without provided Logger and EventDispatcher.');
        }

        $backup = new Backup($profile->getName());

        $this->logger->info(sprintf('About to execute backup for profile: "%s".', $profile->getName()));
        $this->eventDispatcher->dispatch(BackupEvents::BEGIN, new BackupEvent($this, $profile, $backup));

        $terminate = function() use ($profile) {

            try {

                $this->eventDispatcher->dispatch(BackupEvents::TERMINATE, new BackupEvent($profile));
                $this->logger->info(sprintf('Backup for profile "%s" successfully terminated.', $profile->getName()));

            } catch (\Exception $e) {

                $this->logger->alert(sprintf('Could not terminate backup process for profile "%s".', $profile->getName()));

            }
        };

        \Closure::bind($terminate, $this);

        try {

            /**
             * @var WorkflowActivityInterface $activity
             */
            foreach ($this->activities as $activity) {
                $this->executeActivity($activity, $profile, $backup);
            }

            $terminate();

        } catch (EmptySourceException $e) {

            $this->logger->info(sprintf('Backup for profile "%s" didn\'t yield any file for backup.', $profile->getName()));

            $terminate();

        } catch (\Exception $e) {

            $this->eventDispatcher->dispatch(BackupEvents::ERROR, new BackupEvent($this, $profile));
            $this->logger->critical(sprintf('There has been an error while executing backup profile "%s".', $profile->getName()), array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ));

            $terminate();
        }
    }

    /**
     * Execute workflow activity.
     *
     * @param WorkflowActivityInterface $activity Activity to execute.
     * @param ProfileInterface $profile Profile for which activity is being executed.
     * @param BackupInterface $backup Backup for which activity is being executed.
     *
     * @throws \Exception
     */
    protected function executeActivity(WorkflowActivityInterface $activity, ProfileInterface $profile, BackupInterface $backup)
    {
        $activity
            ->setBackup($backup)
            ->setProfile($profile);

        /**
         * @var LoggerAwareInterface $activity
         */
        if ($activity instanceof LoggerAwareInterface) {
            $activity->setLogger($this->logger);
        }

        /**
         * @var EventDispatcherAwareInterface $activity
         */
        if ($activity instanceof EventDispatcherAwareInterface) {
            $activity->setEventDispatcher($this->eventDispatcher);
        }

        try {
            /**
             * @var WorkflowActivityInterface $activity
             */
            $activity->execute();

        } catch (\Exception $e) {

            $this->eventDispatcher->dispatch(BackupEvents::ERROR, new BackupEvent($this, $profile, $backup, $activity));

            throw $e;
        }
    }

    /**
     * Builds default workflow.
     *
     * @return Workflow
     */
    public static function build()
    {
        return new Workflow(array(
            new Fetch(),
            new Process(),
            new Name(),
            new PreRotate(),
            new Push(),
            new PostRotate()
        ));
    }
}

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

use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\WorkflowActivityInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class BackupEvent
 *
 * Backup event is event instance that is dispatched troughout backup library.
 *
 * @package RunOpenCode\Backup\Event
 */
final class BackupEvent extends GenericEvent
{
    /**
     * Dispatched at the begging of the backup process.
     */
    const BEGIN = 'run_open_code.backup.begin';

    /**
     * Dispatched after Source have provided backup files.
     */
    const FETCH = 'run_open_code.backup.fetch';

    /**
     * Dispatched after Processor have finished with processing.
     */
    const PROCESS = 'run_open_code.backup.process';

    /**
     * Dispatched after Namer have finished with naming of backup.
     */
    const NAME = 'run_open_code.backup.name';

    /**
     * Dispatched after PreRotator have nominated backups for rotation.
     */
    const PRE_ROTATE = 'run_open_code.backup.pre_rotate';

    /**
     * Dispatched after backup is pushed to Destination.
     */
    const PUSH = 'run_open_code.backup.push';

    /**
     * Dispatched after PostRotator have nominated backups for rotation.
     */
    const POST_ROTATE = 'run_open_code.backup.post_rotate';

    /**
     * Dispatched always - when backup process is terminated.
     */
    const TERMINATE = 'run_open_code.backup.terminate';

    /**
     * Dispatched on backup error.
     */
    const ERROR = 'run_open_code.backup.error';

    public function __construct(ProfileInterface $profile, BackupInterface $backup = null, WorkflowActivityInterface $activity = null)
    {
        parent::__construct($activity, $arguments = array(
            'profile' => $profile,
            'backup' => $backup
        ));
    }

    /**
     * Get activity which dispatched the event.
     *
     * @return null|WorkflowActivityInterface
     */
    public function getActivity()
    {
        return $this->getSubject();
    }

    /**
     * Get current processing profile.
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->getArgument('profile');
    }

    /**
     * Get current processing backup.
     *
     * @return null|BackupInterface
     */
    public function getBackup()
    {
        return $this->getArgument('backup');
    }
}
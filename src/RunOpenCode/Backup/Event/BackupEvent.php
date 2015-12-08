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
    public function __construct($subject = null, ProfileInterface $profile = null, BackupInterface $backup = null, WorkflowActivityInterface $activity = null)
    {
        parent::__construct($subject, $arguments = array(
            'profile' => $profile,
            'backup' => $backup,
            'activity' => $activity
        ));
    }

    /**
     * Get activity which dispatched the event.
     *
     * @return WorkflowActivityInterface|null
     */
    public function getActivity()
    {
        return $this->getArgument('activity');
    }

    /**
     * Get current processing profile.
     *
     * @return ProfileInterface|null
     */
    public function getProfile()
    {
        return $this->getArgument('profile');
    }

    /**
     * Get current processing backup.
     *
     * @return BackupInterface|null
     */
    public function getBackup()
    {
        return $this->getArgument('backup');
    }
}

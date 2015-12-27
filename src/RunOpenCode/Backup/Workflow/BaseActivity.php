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

use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\WorkflowActivityInterface;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class BaseActivity
 *
 * Prototype for workflow activity.
 *
 * @package RunOpenCode\Backup\Workflow
 */
abstract class BaseActivity implements WorkflowActivityInterface
{
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;

    /**
     * @var ProfileInterface
     */
    protected $profile;

    /**
     * @var BackupInterface
     */
    protected $backup;

    /**
     * Set current backup.
     *
     * @param BackupInterface $backup Current backup.
     * @return BaseActivity $this Fluent interface.
     */
    public function setBackup(BackupInterface $backup)
    {
        $this->backup = $backup;
        return $this;
    }

    /**
     * Set current profile.
     *
     * @param ProfileInterface $profile Current profile.
     * @return BaseActivity $this Fluent interface.
     */
    public function setProfile(ProfileInterface $profile)
    {
        $this->profile = $profile;
        return $this;
    }
}

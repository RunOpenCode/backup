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
namespace RunOpenCode\Backup\Contract;

/**
 * Interface WorkflowActivityInterface
 *
 * Workflow is sequence of activities required to be executed in order to execute backup activity.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface WorkflowActivityInterface
{
    /**
     * @param BackupInterface $backup
     * @return WorkflowActivityInterface $this Fluent interface.
     */
    public function setBackup(BackupInterface $backup);

    /**
     * @param ProfileInterface $profile
     * @return WorkflowActivityInterface $this Fluent interface.
     */
    public function setProfile(ProfileInterface $profile);

    /**
     * Execute workflow activity.
     */
    public function execute();
}
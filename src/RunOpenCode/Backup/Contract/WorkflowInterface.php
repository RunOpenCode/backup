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
 * Interface WorkflowInterface
 *
 * Workflow is entry point of backup workflow that executes workflow activities in given sequence.
 *
 * @package RunOpenCode\Backup\Workflow
 */
interface WorkflowInterface
{
    /**
     * Execute backup profile in defined workflow.
     *
     * @param ProfileInterface $profile Backup profile to execute.
     */
    public function execute(ProfileInterface $profile);
}
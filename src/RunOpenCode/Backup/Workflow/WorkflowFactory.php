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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class WorkflowFactory
{
    private function __construct() { }

    /**
     * Builds default workflow.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @return Workflow
     */
    public static function build(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        return new Workflow($eventDispatcher, $logger, array(
            new Fetch(),
            new Process(),
            new Name(),
            new PreRotate(),
            new Push(),
            new PostRotate()
        ));
    }
}
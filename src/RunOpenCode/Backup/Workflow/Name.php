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

use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class Name
 *
 * Activity "Name": provide name for backup.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class Name extends BaseActivity implements LoggerAwareInterface, EventDispatcherAwareInterface
{
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {

            $this->backup->setName($this->profile->getNamer()->getName());
            $this->getEventDispatcher()->dispatch(BackupEvents::NAME, new BackupEvent($this, $this->profile, $this->backup, $this));

        } catch (\Exception $e) {

            $this->getLogger()->error(sprintf('Could not set name of new backup for profile "%s".', $this->profile->getName()), array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ));

            throw $e;
        }

        $this->getLogger()->info(sprintf('Naming successfully completed, backup name: "%s".', $this->backup->getName()));
    }
}

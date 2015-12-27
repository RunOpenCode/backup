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

use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Exception\EmptySourceException;

/**
 * Class Fetch
 *
 * Activity "Fetch": fetch backups from sources.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class Fetch extends BaseActivity
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {

            $this->backup->addFiles($this->profile->getSource()->fetch());
            $this->getEventDispatcher()->dispatch(BackupEvents::FETCH, new BackupEvent($this, $this->profile, $this->backup, $this));

        } catch (\Exception $e) {

            $this->getLogger()->error(sprintf('Could not fetch source files for profile "%s".', $this->profile->getName()), array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ));

            throw $e;
        }

        if (count($this->backup->getFiles()) == 0) {

            throw new EmptySourceException('Nothing to backup.');

        } else {

            $this->getLogger()->info(sprintf('Source files successfully fetched, %s total files are scheduled for backup.', count($this->backup->getFiles())));

        }
    }
}

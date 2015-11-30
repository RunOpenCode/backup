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
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class Process
 *
 * Activity "Process": process backup files.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class Process extends BaseActivity implements LoggerAwareInterface, EventDispatcherAwareInterface
{
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;

    public function execute()
    {
        try {

            $files = $this->backup->getFiles();
            $countIn = count($files);

            $files = $this->profile->getProcessor()->process($files);
            $countOut = count($files);

            $this->backup->setFiles($files);

            $this->getLogger()->info(sprintf('Source files successfully processed, %s files in, %s out.', $countIn, $countOut));
            $this->getEventDispatcher()->dispatch(BackupEvent::PROCESS, new BackupEvent($this->profile, $this->backup, $this));


        } catch (\Exception $e) {

            $this->getLogger()->error(sprintf('Could not process source files for profile "%s".', $this->profile->getName()), array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ));

            throw $e;
        }
    }
}
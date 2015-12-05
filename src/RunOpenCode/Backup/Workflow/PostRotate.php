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
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class PostRotate
 *
 * Activity "PostRotation": nominate backups for rotation after backup is uploaded to destination.
 *
 * @package RunOpenCode\Backup\Workflow
 */
class PostRotate extends BaseActivity implements LoggerAwareInterface, EventDispatcherAwareInterface
{
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {

            $nominations = $this->profile->getPostRotator()->nominate($this->profile->getDestination()->all());

            if ($count = count($nominations) > 0) {

                /**
                 * @var BackupInterface $nomination
                 */
                foreach ($nominations as $nomination) {

                    $this->profile->getDestination()->delete($nomination->getName());
                }
            }

            $this->getLogger()->info(sprintf('Post-rotation successfully executed, %s backups rotated.', $count));
            $this->getEventDispatcher()->dispatch(BackupEvents::POST_ROTATE, new BackupEvent($this, $this->profile, $this->backup, $this));

        } catch (\Exception $e) {

            $this->getLogger()->error(sprintf('Could not execute post-rotation for profile "%s".', $this->profile->getName()), array(
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
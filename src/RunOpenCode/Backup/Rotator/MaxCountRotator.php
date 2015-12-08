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
namespace RunOpenCode\Backup\Rotator;

use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\RotatorInterface;

/**
 * Class MaxCountRotator
 *
 * Rotator that nominates old backups when number of current backups exceed allowed number of backups.
 *
 * @package RunOpenCode\Backup\Rotator
 */
final class MaxCountRotator implements RotatorInterface
{
    /**
     * @var integer
     */
    private $count;

    public function __construct($count)
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('You need to allow at least one backup file to be created.');
        }
        $this->count = $count;
    }

    /**
     * {@inheritdoc}
     */
    public function nominate(array $backups)
    {
        if (($currentCount = count($backups)) > $this->count) {

            $list = array();

            /**
             * @var BackupInterface $backup
             */
            foreach ($backups as $backup) {
                $list[$backup->getCreatedAt()->getTimestamp()] = $backup;
            }

            ksort($list);

            $nominations = array();

            /**
             * @var BackupInterface $backup
             */
            foreach ($list as $backup) {
                $nominations[] = $backup;
                $currentCount--;

                if ($currentCount <= $this->count) {
                    break;
                }
            }

            return $nominations;

        } else {
            return array();
        }
    }
}

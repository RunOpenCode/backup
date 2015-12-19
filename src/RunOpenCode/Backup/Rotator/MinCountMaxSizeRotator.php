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
use RunOpenCode\Backup\Utils\Filesize;

/**
 * Class MinCountMaxSizeRotator
 *
 * Rotator that nominates old backups when total backup size exceed maximum allowed backup size, but it will keep minimum
 * required number of backups.
 *
 * @package RunOpenCode\Backup\Rotator
 */
final class MinCountMaxSizeRotator implements RotatorInterface
{
    /**
     * @var integer
     */
    private $maxSize;
    /**
     * @var integer
     */
    private $minCount;

    public function __construct($minCount, $maxSize)
    {
        if ($minCount < 1) {
            throw new \InvalidArgumentException('At least one file should be set as minimum backup count.');
        }

        $this->minCount = $minCount;
        $this->maxSize = Filesize::getBytes($maxSize);
    }

    /**
     * {@inheritdoc}
     */
    public function nominate(array $backups)
    {
        $list = array();
        $currentSize = 0;
        /**
         * @var BackupInterface $backup
         */
        foreach ($backups as $backup) {
            $list[$backup->getCreatedAt()->getTimestamp()] = $backup;
            $currentSize += $backup->getSize();
        }
        if ($currentSize > $this->maxSize) {
            ksort($list);
            $nominations = array();
            /**
             * @var BackupInterface $backup
             */
            foreach ($list as $backup) {
                if (count($list) - count($nominations) <= $this->minCount) {
                    break;
                }
                $nominations[] = $backup;
                $currentSize -= $backup->getSize();
                if ($currentSize <= $this->maxSize) {
                    break;
                }
            }
            return $nominations;
        } else {
            return array();
        }
    }
}

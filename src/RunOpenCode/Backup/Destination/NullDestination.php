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
namespace RunOpenCode\Backup\Destination;

use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\DestinationInterface;

/**
 * Class NullDestination
 *
 * Null destination does not contains or store any backups.
 *
 * @package RunOpenCode\Backup\Destination
 */
class NullDestination implements DestinationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator(array());
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        // Do nothing.
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        throw new \RuntimeException('Null destination does not have backups to fetch.');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        throw new \RuntimeException('Null destination does not have backups to delete.');
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return false;
    }
}

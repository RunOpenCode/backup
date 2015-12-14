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
 * Class BaseDestination
 *
 * BaseDestination provides prototype for concrete destination implementation.
 *
 * @package RunOpenCode\Backup\Destination
 */
abstract class BaseDestination implements DestinationInterface
{
    /**
     * @var BackupInterface[]
     */
    protected $backups;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return new \ArrayIterator($this->backups);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return $this->backups[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return array_key_exists($name, $this->backups);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return $this->backups;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return count($this->backups);
    }

    /**
     * Load backups from destination.
     *
     * @return BackupInterface[]
     */
    abstract protected function load();
}

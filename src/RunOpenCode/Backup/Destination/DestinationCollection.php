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
 * Class DestinationCollection
 *
 * Collection of destinations enables you to backup on several different destinations at once.
 *
 * @package RunOpenCode\Backup\Destination
 */
final class DestinationCollection implements DestinationInterface
{
    /**
     * @var DestinationInterface[]
     */
    private $destinations;

    public function __construct(array $destinations = array())
    {
        $this->destinations = array();

        foreach ($destinations as $destination) {
            $this->addDestination($destination);
        }
    }

    public function addDestination(DestinationInterface $destination)
    {
        $this->destinations[] = $destination;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        foreach ($this->destinations as $destination) {
            $destination->push($backup);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->destinations[0]->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->destinations[0]->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        foreach ($this->destinations as $destination) {
            $destination->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->destinations[0]->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->destinations[0]->getIterator();
    }
}
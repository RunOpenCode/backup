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
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class ReplicatedDestination
 *
 * Replicated destination enables to you to backup on one master and one slave destination where failed backup attempt
 * to slave destination will not fail backup process.
 *
 * @package RunOpenCode\Backup\Destination
 */
final class ReplicatedDestination implements DestinationInterface
{

    use LoggerAwareTrait;

    /**
     * @var DestinationInterface
     */
    private $master;

    /**
     * @var DestinationInterface
     */
    private $slave;

    /**
     * @var bool
     */
    private $atomic;

    public function __construct(DestinationInterface $master, DestinationInterface $slave, $atomic = false)
    {
        $this->master = $master;
        $this->slave = $slave;
        $this->atomic = $atomic;
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        $this->master->push($backup);

        try {
            $this->slave->push($backup);
        } catch (\Exception $e) {

            if ($this->atomic) {
                throw $e;
            } elseif ($this->getLogger()) {
                $this->getLogger()->error('Unable to backup to slave destination.', array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->master->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
       return $this->master->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $this->master->delete($name);

        try {
            $this->slave->delete($name);
        } catch (\Exception $e) {

            if ($this->atomic) {
                throw $e;
            } else {

                $this->getLogger()->error(sprintf('Unable to delete backup "%s" from slave destination.', $name), array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->master->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->master->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->master->count();
    }
}

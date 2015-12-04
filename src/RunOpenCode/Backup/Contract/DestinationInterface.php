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
namespace RunOpenCode\Backup\Contract;

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Exception\DestinationException;

/**
 * Interface DestinationInterface
 *
 * Destination is backup storage location.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface DestinationInterface extends \IteratorAggregate
{
    /**
     * Push backup to destination.
     *
     * @param BackupInterface $backup
     * @throws DestinationException
     */
    public function push(BackupInterface $backup);

    /**
     * Get backup.
     *
     * @param string $key
     * @return BackupInterface
     */
    public function get($key);

    /**
     * Check if backup exists.
     *
     * @param $key
     * @return boolean
     */
    public function has($key);

    /**
     * Delete backup.
     *
     * @param $key
     * @throws DestinationException
     */
    public function delete($key);

    /**
     * Get all backups.
     *
     * @return BackupInterface[]
     */
    public function all();
}
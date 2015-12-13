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
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Exception\DestinationException;

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
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $this->doDelete($name);

        if (!is_null($this->backups)) {
            unset($this->backups[$name]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        $backupDirectory = $this->getDirectoryForBackup($backup);
        $removedBackupFiles = $this->getFiles($backupDirectory);

        /**
         * @var FileInterface $backupFile
         */
        foreach ($backup->getFiles() as $backupFile) {

            if (isset($removedBackupFiles[$backupFile->getRelativePath()])) {
                unset($removedBackupFiles[$backupFile->getRelativePath()]);
            }

            $this->pushFile($backupDirectory, $backupFile);
        }

        foreach ($removedBackupFiles as $removedBackupFile) {
            $this->removeFile($backupDirectory, $removedBackupFile);
        }

        $this->removeEmptyDirectories($backupDirectory);

        if (!empty($this->backups)) {
            $this->backups[] = $backup;
        }
    }

    /**
     * Get backup directory where backups will be transferred. If directory does not exists, it will be created.
     *
     * @param BackupInterface $backup Backup for which directory is fetched/created.
     * @return string Path to created backup directory.
     *
     * @throws DestinationException
     */
    protected abstract function getDirectoryForBackup(BackupInterface $backup);

    /**
     * Load backups from destination.
     *
     * @return BackupInterface[]
     */
    protected abstract function load();

    /**
     * Delete backup from destination.
     *
     * @param string $name Backup name to delete.
     *
     * @throws DestinationException
     */
    protected abstract function doDelete($name);

    /**
     * Get all files in path.
     *
     * @param string $path Path to directory where files residue.
     * @return FileInterface[] List of files in given location
     */
    protected abstract function getFiles($path);

    /**
     * Push file to backup destination, or update it if exist and it is newer.
     *
     * @param string $backupDirectory Backup directory in backup destination where to push file.
     * @param FileInterface $backupFile File to push to destination.
     *
     * @throws DestinationException
     */
    protected abstract function pushFile($backupDirectory, FileInterface $backupFile);

    /**
     * Delete backup file from destination.
     *
     * @param string $backupDirectory Directory where backup file residue.
     * @param FileInterface $backupFile Backup file to remove.
     *
     * @throws DestinationException
     */
    protected abstract function removeFile($backupDirectory, FileInterface $backupFile);

    /**
     * Remove empty directories from backup destination.
     *
     * @param string $backupDirectory Backup directory to cleanup.
     *
     * @throws DestinationException
     */
    protected abstract function removeEmptyDirectories($backupDirectory);
}
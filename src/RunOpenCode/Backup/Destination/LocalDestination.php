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

use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Exception\DestinationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class LocalDestination
 *
 * Stream destination is local, mountable, destination.
 *
 * @package RunOpenCode\Backup\Destination
 */
class LocalDestination extends BaseDestination
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct($directory, Filesystem $filesystem = null)
    {
        $this->directory = rtrim($directory, '/\\');
        $this->filesystem = is_null($filesystem) ? new Filesystem() : $filesystem;

        if (!$this->filesystem->exists($this->directory)) {

            $this->filesystem->mkdir($this->directory);

        } elseif (!is_dir($this->directory)) {
            throw new \RuntimeException(sprintf('Provided location "%s" is not directory.', $this->directory));
        } elseif (!is_writable($this->directory)) {
            throw new \RuntimeException(sprintf('Provided location "%s" is not writeable.', $this->directory));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        try {
            $this->filesystem->remove(sprintf('%s%s%s', rtrim($this->directory, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $name));
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to remove backup "%s" from stream destination "%s".', $name, $this->directory), 0, $e);
        }

        if (!is_null($this->backups)) {
            unset($this->backups[$name]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        $backupDirectory = $this->directory . DIRECTORY_SEPARATOR . $backup->getName();

        try {
            $this->filesystem->mkdir($backupDirectory);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to create backup directory "%s" for backup "%s" in local destination.', $backupDirectory, $backup->getName()));
        }

        $removedBackupFiles = $this->getFiles($backupDirectory);

        /**
         * @var FileInterface $backupFile
         */
        foreach ($backup->getFiles() as $backupFile) {

            if (isset($removedBackupFiles[$backupFile->getRelativePath()])) {
                unset($removedBackupFiles[$backupFile->getRelativePath()]);
            }

            try {
                $this->filesystem->copy($backupFile->getPath(), sprintf('%s%s%s', $backupDirectory, DIRECTORY_SEPARATOR, $backupFile->getRelativePath()));
            } catch (\Exception $e) {
                throw new DestinationException(sprintf('Unable to backup file "%s" to destination "%s".', $backupFile->getPath(), $this->directory), 0, $e);
            }
        }

        /**
         * @var FileInterface $removedBackupFile
         */
        foreach ($removedBackupFiles as $removedBackupFile) {

            $path = $backupDirectory . DIRECTORY_SEPARATOR . $removedBackupFile->getRelativePath();

            try {
                $this->filesystem->remove($path);
            } catch (\Exception $e) {
                throw new DestinationException(sprintf('Unable to cleanup backup destination "%s" after backup process, file "%s" could not be removed.', $backupDirectory, $path), 0, $e);
            }
        }

        $this->removeEmptyDirectories($backupDirectory);

        if (is_array($this->backups)) {
            $this->backups[$backup->getName()] = $backup;
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function getFiles($path)
    {
        $result = array();

        foreach (Finder::create()->in($path)->files() as $file) {
            $file = File::fromSplFileInfo($file, $path);
            $result[$file->getRelativePath()] = $file;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function load()
    {
        $this->backups = array();

        /**
         * @var \SplFileInfo $backupDirectory
         */
        foreach (Finder::create()->in($this->directory)->depth(0)->directories()->sortByModifiedTime() as $backupDirectory) {

            $backup = new Backup($backupDirectory->getBasename(), $this->getFiles($backupDirectory->getPathname()), 0, $backupDirectory->getCTime(), $backupDirectory->getMTime());

            $this->backups[$backup->getName()] = $backup;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function removeEmptyDirectories($backupDirectory)
    {
        /**
         * @var \SplFileInfo $dir
         */
        foreach (Finder::create()->directories()->in($backupDirectory)->depth(0) as $dir) {

            if (Finder::create()->files()->in($dir->getPathname())->count() > 0) {
                $this->removeEmptyDirectories($dir->getPathname());
            } else {
                $this->filesystem->remove($dir->getPathname());
            }
        }
    }
}

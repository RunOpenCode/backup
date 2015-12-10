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
use RunOpenCode\Backup\Contract\DestinationInterface;
use RunOpenCode\Backup\Exception\DestinationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class StreamDestination
 *
 * Stream destination is local, mountable, destination.
 *
 * @package RunOpenCode\Backup\Destination
 */
class StreamDestination implements DestinationInterface
{
    /**
     * @var BackupInterface[]
     */
    protected $backups;

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
        $this->directory = $directory;
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
    public function push(BackupInterface $backup)
    {
        $backupDirectory = sprintf('%s%s%s', rtrim($this->directory, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $backup->getName());
        $this->filesystem->mkdir($backupDirectory);

        $existingBackupFiles = array();

        foreach (Finder::create()->in($backupDirectory)->files() as $existingFile) {
            $file = File::fromLocal($existingFile, $backupDirectory);
            $existingBackupFiles[$file->getRelativePath()] = $file;
        }

        foreach ($backup->getFiles() as $backupFile) {

            try {
                $this->filesystem->copy($backupFile->getPath(), $filePath = sprintf('%s%s%s', $backupDirectory, DIRECTORY_SEPARATOR, $backupFile->getRelativePath()));
                $this->filesystem->touch($filePath, $backupFile->getModifiedAt()->getTimestamp());
            } catch (\Exception $e) {
                throw new DestinationException(sprintf('Unable to backup file "%s" to destination "%s".', $backupFile->getPath(), $this->directory), 0, $e);
            }

            if (array_key_exists($backupFile->getRelativePath(), $existingBackupFiles) && $existingBackupFiles[$backupFile->getRelativePath()]) {
                unset($existingBackupFiles[$backupFile->getRelativePath()]);
            }
        }

        $this->filesystem->remove($existingBackupFiles);
        $this->removeEmptyDirectories($backupDirectory);

        if (!empty($this->backups)) {
            $this->backups[] = $backup;
        }
    }

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
    public function all()
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return $this->backups;
    }

    /**
     * Load backups from destination.
     *
     * @return BackupInterface[]
     */
    protected function load()
    {
        $this->backups = array();

        /**
         * @var \SplFileInfo $backupDirectory
         */
        foreach (Finder::create()->in($this->directory)->depth(0)->directories()->sortByModifiedTime() as $backupDirectory) {

            $backup = new Backup($backupDirectory->getBasename(), array(), 0, $backupDirectory->getCTime(), $backupDirectory->getMTime());

            /**
             * @var \SplFileInfo $backupFile
             */
            foreach (Finder::create()->in($backupDirectory->getPathname())->files() as $backupFile) {

                $backup->addFile(File::fromSplFileInfo($backupFile, $backupDirectory->getPathname()));
            }

            $this->backups[$backup->getName()] = $backup;
        }
    }

    /**
     * Remove empty directories from destination.
     *
     * @param $backupDirectory
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

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getIterator());
    }
}

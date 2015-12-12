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
    public function push(BackupInterface $backup)
    {
        // Prepare destination.
        $backupDirectory = $this->directory . DIRECTORY_SEPARATOR . $backup->getName();
        $this->filesystem->mkdir($backupDirectory);

        // Get current in backup location (if it is an incremental backup).
        $existingBackupFiles = $this->getFiles($backupDirectory);

        foreach ($backup->getFiles() as $backupFile) {

            try {
                // Overwrite old, copy new to destination.
                $this->filesystem->copy($backupFile->getPath(), sprintf('%s%s%s', $backupDirectory, DIRECTORY_SEPARATOR, $backupFile->getRelativePath()));
            } catch (\Exception $e) {
                throw new DestinationException(sprintf('Unable to backup file "%s" to destination "%s".', $backupFile->getPath(), $this->directory), 0, $e);
            }

            // If existing file is overwritten, remove it from the list of current files in backup destination.
            if (isset($existingBackupFiles[$backupFile->getRelativePath()])) {
                unset($existingBackupFiles[$backupFile->getRelativePath()]);
            }
        }

        try {
            // Remove deleted files from source from destination.
            $this->filesystem->remove(array_map(function(File $file) { return $file->getPath(); }, $existingBackupFiles));

            // Cleanup empty directories, there is no need to keep them.
            $this->removeEmptyDirectories($backupDirectory);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to backup cleanup destination "%s" after backup process.', $this->directory), 0, $e);
        }


        // Don't reload, just add new backup to list if possible.
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
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getIterator());
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

            $backup = new Backup($backupDirectory->getBasename(), $this->getFiles($backupDirectory->getPathname()), 0, $backupDirectory->getCTime(), $backupDirectory->getMTime());

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
     * Get all files in path.
     *
     * @param string $path Path to
     * @return File[] List of files in given location
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
}

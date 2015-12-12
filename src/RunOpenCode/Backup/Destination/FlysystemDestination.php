<?php

namespace RunOpenCode\Backup\Destination;

use League\Flysystem\FilesystemInterface;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\DestinationInterface;
use RunOpenCode\Backup\Exception\DestinationException;

class FlysystemDestination implements DestinationInterface
{
    /**
     * @var FilesystemInterface
     */
    protected $flysystem;

    /**
     * @var array
     */
    protected $backups;

    public function __construct(FilesystemInterface $flysystem)
    {
        $this->flysystem = $flysystem;
    }

    /**
     * {@inheritdoc}
     */
    public function push(BackupInterface $backup)
    {
        if (!$this->flysystem->has($backup->getName())) {
            $this->flysystem->createDir($backup->getName());
        }

        $existingBackupFiles = $this->getFiles($backup->getName());

        foreach ($backup->getFiles() as $backupFile) {

            try {

                $resource = fopen($backupFile->getPath(), 'r');

                if (
                    isset($existingBackupFiles[$backupFile->getRelativePath()])
                    &&
                    $backupFile->getModifiedAt() > new \DateTime('@'.$this->flysystem->getTimestamp($backup->getName() . '/' . $backupFile->getRelativePath()))
                ) {

                    $this->flysystem->updateStream($backup->getName() . '/' . $backupFile->getRelativePath(),  $resource);

                } else {

                    $this->flysystem->putStream($backup->getName() . '/' . $backupFile->getRelativePath(),  $resource);

                }

                if (isset($existingBackupFiles[$backupFile->getRelativePath()])) {
                    unset($existingBackupFiles[$backupFile->getRelativePath()]);
                }

                fclose($resource);

            } catch (\Exception $e) {
                throw new DestinationException(sprintf('Unable to backup file "%s" to flysystem destination.', $backupFile->getPath()), 0, $e);
            }
        }

        try {

            foreach ($existingBackupFiles as $removedBackupFile) {
                $this->flysystem->delete($backup->getName() . '/' . $removedBackupFile->getRelativePath());
            }

            $this->removeEmptyDirectories($backup->getName());

        } catch (\Exception $e) {
            throw new DestinationException('Unable to backup cleanup flysystem destination after backup process.', 0, $e);
        }

        if (!empty($this->backups)) {
            $this->backups[] = $backup;
        }
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
            $this->flysystem->deleteDir($name);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to remove backup "%s" from flysystem destination.', $name), 0, $e);
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
    public function getIterator()
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return new \ArrayIterator($this->backups);
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
         * @var \SplFileInfo $content
         */
        foreach ($this->flysystem->listContents() as $content) {

            if ($content['type'] == 'dir') {

                $backup = new Backup($content['path'], array(), $content['timestamp'], $content['timestamp']);

                /**
                 * @var \SplFileInfo $backupFile
                 */
                foreach ($this->flysystem->listContents($content['path'], true) as $backupFile) {

                    if ($backupFile['type'] == 'file') {

                        $backup->addFile(new File(
                            $backupFile['basename'],
                            $backupFile['path'],
                            $content['path'],
                            $backupFile['size'],
                            $backupFile['timestamp'],
                            $backupFile['timestamp']
                        ));
                    }
                }

                $this->backups[$backup->getName()] = $backup;
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

    /**
     * Remove empty directories from destination.
     *
     * @param string $backupName
     */
    protected function removeEmptyDirectories($backupName)
    {
        /**
         * @var \SplFileInfo $dir
         */
        foreach ($this->flysystem->listContents($backupName) as $dir) {

            if ($dir['type'] != 'dir') {
                continue;
            }

            if (count($this->flysystem->listContents($dir['path'])) > 0) {
                $this->removeEmptyDirectories($dir['path']);
            } else {
                $this->flysystem->deleteDir($dir['path']);
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

        /**
         * @var \SplFileInfo $file
         */
        foreach ($this->flysystem->listContents($path, true) as $file) {

            if ($file['type'] == 'file') {
                $file = new File(
                    $file['basename'],
                    $file['path'],
                    $path,
                    $file['size'],
                    $file['timestamp'],
                    $file['timestamp']
                );
                $result[$file->getRelativePath()] = $file;
            }
        }

        return $result;
    }
}

<?php

namespace RunOpenCode\Backup\Destination;

use League\Flysystem\FilesystemInterface;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Exception\DestinationException;

class FlysystemDestination extends BaseDestination
{
    /**
     * @var FilesystemInterface
     */
    protected $flysystem;

    public function __construct(FilesystemInterface $flysystem)
    {
        $this->flysystem = $flysystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDirectoryForBackup(BackupInterface $backup)
    {
        if (!$this->flysystem->has($backup->getName())) {

            if (!$this->flysystem->createDir($backup->getName())) {
                throw new DestinationException(sprintf('Unable to create backup directory "%s" in flysystem destination.', $backup->getName()));
            }
        }

        return $backup->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFiles($path)
    {
        $result = array();

        /**
         * @var \SplFileInfo $file
         */
        foreach ($this->flysystem->listContents($path, true) as $file) {

            if ($file['type'] == 'file') {
                $file = File::fromFlysystemMetadata($file, $path);
                $result[$file->getRelativePath()] = $file;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function pushFile($backupDirectory, FileInterface $backupFile)
    {
        $path = $backupDirectory . '/' . $backupFile->getRelativePath();

        try {

            if ($this->flysystem->has($path)) {

                if ($backupFile->getModifiedAt() > new \DateTime('@' . $this->flysystem->getTimestamp($path))) {
                    $resource = fopen($backupFile->getPath(), 'r');
                    $this->flysystem->updateStream($path,  $resource);
                    fclose($resource);
                }

            } else {
                $resource = fopen($backupFile->getPath(), 'r');
                $this->flysystem->putStream($path,  $resource);
                fclose($resource);
            }

        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to backup file "%s" to flysystem destination.', $backupFile->getPath()), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function load()
    {
        $this->backups = array();

        /**
         * @var \SplFileInfo $content
         */
        foreach ($this->flysystem->listContents() as $content) {

            if ($content['type'] == 'dir') {

                $backup = new Backup($content['basename'], $this->getFiles($content['path']), $content['timestamp'], $content['timestamp']);

                $this->backups[$backup->getName()] = $backup;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($name)
    {
        try {
            $this->flysystem->deleteDir($name);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to remove backup "%s" from flysystem destination.', $name), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function removeFile($backupDirectory, FileInterface $backupFile)
    {
        $path = $backupDirectory . '/' . $backupFile->getRelativePath();

        try {
            $this->flysystem->delete($path);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to cleanup backup destination "%s" after backup process, file "%s" could not be removed.', $backupDirectory, $path), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
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
}

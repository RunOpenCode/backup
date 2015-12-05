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
    protected $flysystem;

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
        // TODO: Implement push() method.
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return $this->backups[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        if (is_null($this->backups)) {
            $this->load();
        }

        return array_key_exists($key, $this->backups);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        try {
            $this->flysystem->deleteDir($key);
        } catch (\Exception $e) {
            throw new DestinationException(sprintf('Unable to remove backup "%s" from flysystem destination.', $key), 0, $e);
        }

        if (!is_null($this->backups)) {
            unset($this->backups[$key]);
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
        foreach ($contents = $this->flysystem->listContents() as $content) {

            if ($content->isDir()) {

                $backup = new Backup($content->getBasename(), array(), $content->getCTime(), $content->getMTime());

                /**
                 * @var \SplFileInfo $backupFile
                 */
                foreach ($backupFiles = $this->flysystem->listContents($content->getBasename(), true) as $backupFile) {

                    if ($backupFile->isFile()) {

                        $backup->addFile(File::fromSplFileInfo($backupFile));
                    }
                }

                $this->backups[$backup->getName()] = $backup;
            }
        }
    }
}
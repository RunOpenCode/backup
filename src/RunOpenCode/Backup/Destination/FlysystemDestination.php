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
        throw new DestinationException('Flysystem is not implemented yet.');
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

            if ($content->isDir()) {

                $backup = new Backup($content->getBasename(), array(), $content->getCTime(), $content->getMTime());

                /**
                 * @var \SplFileInfo $backupFile
                 */
                foreach ($this->flysystem->listContents($content->getBasename(), true) as $backupFile) {

                    if ($backupFile->isFile()) {

                        $backup->addFile(File::fromSplFileInfo($backupFile));
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
}

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

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Contract\DestinationInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class StreamDestination
 *
 * Stream destination is local, mountable, destination.
 *
 * @package RunOpenCode\Backup\Destination
 */
class StreamDestination implements DestinationInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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

        if (!$filesystem->exists($this->directory)) {

            $filesystem->mkdir($this->directory);

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

        if (!$this->filesystem->exists($backupDirectory)) {

            $this->filesystem->mkdir($backupDirectory);

        } else {
            $currentFiles = Finder::create()->in($backupDirectory)->files();
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
        if (is_null($this->backups)) {
            $this->load();
        }

        $this->filesystem->remove(sprintf('%s%s%s', rtrim($this->directory, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $key));
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

        $backupDirectories = Finder::create()->in($this->directory)->depth(0)->directories()->sortByModifiedTime();

        foreach ($backupDirectories as $backupDirectory) {

            $backup = new Backup(basename($backupDirectory), array(), 0, filectime($backupDirectory), filemtime($backupDirectory));

            foreach ($backupFiles = Finder::create()->in($backupDirectory)->files() as $backupFile) {

                $backup->addFile(File::fromLocal($backupFile, $backupDirectory));
            }

            $this->backups[$backup->getName()] = $backup;
        }
    }
}
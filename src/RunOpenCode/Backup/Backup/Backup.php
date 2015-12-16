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

namespace RunOpenCode\Backup\Backup;

use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Utils\Filename;

/**
 * Class Backup.
 *
 * Backup is abstraction of collection of files for backup.
 */
final class Backup implements BackupInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var FileInterface[]
     */
    private $files;

    /**
     * @var int
     */
    private $size;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     */
    private $modifiedAt;

    public function __construct($name, array $files = array(), $size = 0, $createdAt = null, $modifiedAt = null)
    {
        $this->name = Filename::sanitize($name);
        $this->size = $size;
        $this->createdAt = is_null($createdAt) ? new \DateTime('now') : $createdAt;
        $this->modifiedAt = is_null($modifiedAt) ? new \DateTime('now') : $modifiedAt;

        if (count($files)) {
            $this->setFiles($files);
        } else {
            $this->files = array();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = Filename::sanitize($name);

        return $this;
    }

    public function addFiles(array $files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    public function addFile(FileInterface $file)
    {
        $this->files[] = $file;
        $this->size += $file->getSize();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
        $this->size = 0;
        foreach ($this->files as $file) {
            $this->size += $file->getSize();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = \DateTimeImmutable::createFromMutable((is_integer($createdAt) ? date_timestamp_set(new \DateTime(), $createdAt) : $createdAt));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = \DateTimeImmutable::createFromMutable((is_integer($modifiedAt) ? date_timestamp_set(new \DateTime(), $modifiedAt) : $modifiedAt));

        return $this;
    }
}

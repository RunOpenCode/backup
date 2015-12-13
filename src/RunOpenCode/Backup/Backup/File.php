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

/**
 * Class File
 *
 * Backup file abstraction.
 *
 * @package RunOpenCode\Backup\Backup
 */
final class File implements FileInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string;
     */
    private $rootPath;

    /**
     * @var string;
     */
    private $relativePath;

    /**
     * @var integer
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

    public function __construct($path, $rootPath, $size, $createdAt, $modifiedAt)
    {
        $this->path = $path;
        $this->rootPath = rtrim(is_null($rootPath) ? '' : $rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->size = $size;
        $this->createdAt = (is_numeric($createdAt)) ? date_timestamp_set(new \DateTime(), $createdAt) : clone $createdAt;
        $this->modifiedAt = (is_numeric($modifiedAt)) ? date_timestamp_set(new \DateTime(), $modifiedAt) : clone $modifiedAt;
        $this->relativePath = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath()
    {
        if (is_null($this->relativePath)) {

            $pos = strpos($this->path, $this->rootPath);

            if ($pos === 0) {
                $this->relativePath = substr_replace($this->path, '', $pos, strlen($this->rootPath));
            } else {
                $this->relativePath = $this->path;
            }
        }

        return $this->relativePath;
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
    public function getCreatedAt()
    {
        return clone $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedAt()
    {
        return clone $this->modifiedAt;
    }

    /**
     * Create File instance from local, mounted filesystem.
     *
     * @param string $path Path to file.
     * @param null|string $rootPath Root path of file.
     * @return File Created backup file instance.
     */
    public static function fromLocal($path, $rootPath = null)
    {
        return new static(
            $path,
            $rootPath,
            filesize($path),
            filectime($path),
            filemtime($path)
        );
    }

    /**
     * Create file instance from \SplFileInfo instance.
     *
     * @param \SplFileInfo $file
     * @param null|string $rootPath Root path of file.
     * @return File
     */
    public static function fromSplFileInfo(\SplFileInfo $file, $rootPath = null)
    {
        return new static(
            $file->getPathname(),
            $rootPath,
            $file->getSize(),
            $file->getCTime(),
            $file->getMTime()
        );
    }

    /**
     * Create file instance from Flysystem file metadata.
     *
     * @param array $metadata Flysystem file metadata.
     * @param null|string $rootPath Root path of file.
     * @return File
     */
    public static function fromFlysystemMetadata(array $metadata, $rootPath = null)
    {
        return new static(
            $metadata['path'],
            $rootPath,
            $metadata['size'],
            $metadata['timestamp'],
            $metadata['timestamp']
        );
    }
}

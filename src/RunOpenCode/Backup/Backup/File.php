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
    private $name;

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

    public function __construct($name, $path, $rootPath, $size, $createdAt, $modifiedAt)
    {
        $this->name = $name;
        $this->path = $path;
        $this->rootPath = rtrim(is_null($rootPath) ? '' : $rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $pos = strpos($this->path, $this->rootPath);

        if ($pos === 0) {
            $this->relativePath = substr_replace($this->path, '', $pos, strlen($this->rootPath));
        } else {
            $this->relativePath = $this->path;
        }

        $this->size = $size;
        $this->createdAt = \DateTimeImmutable::createFromMutable((is_integer($createdAt) ? date_timestamp_set(new \DateTime(), $createdAt) : $createdAt));
        $this->modifiedAt = \DateTimeImmutable::createFromMutable((is_integer($modifiedAt) ? date_timestamp_set(new \DateTime(), $modifiedAt) : $modifiedAt));
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
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Create File instance from local, mounted filesystem.
     *
     * @param string $path Path to file.
     * @param null|string $rootPath Root path of file.
     * @param null|string $name Filename to use instead of original one (if provided).
     * @return File Created backup file instance.
     */
    public static function fromLocal($path, $rootPath = null, $name = null)
    {
        return new static(
            is_null($name) ? basename($path) : $name,
            $path,
            $rootPath,
            filesize($path),
            filectime($path),
            filemtime($path)
        );
    }
}
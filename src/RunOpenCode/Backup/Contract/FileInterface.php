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
namespace RunOpenCode\Backup\Contract;

/**
 * Interface FileInterface
 *
 * Abstraction of one backup file.
 *
 * @package RunOpenCode\Backup
 */
interface FileInterface
{
    /**
     * Get file name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get file path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Get file root path.
     *
     * @return string
     */
    public function getRootPath();

    /**
     * Get file relative path having in mind its root path.
     *
     * @return mixed
     */
    public function getRelativePath();

    /**
     * Get file size.
     *
     * @return int
     */
    public function getSize();

    /**
     * Get file creation datetime.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt();

    /**
     * Get file modification datetime.
     *
     * @return \DateTimeInterface
     */
    public function getModifiedAt();
}
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
 * Interface BackupInterface
 *
 * Backup is collection of files which ought to be backed up.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface BackupInterface
{
    /**
     * Get sanitized backup name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set backup name.
     *
     * @param string $name Backup name.
     * @return BackupInterface $this Fluent interface.
     */
    public function setName($name);

    /**
     * Add files to backup.
     *
     * @param FileInterface[] $files
     * @return BackupInterface $this Fluent interface.
     */
    public function addFiles(array $files);

    /**
     * Add file to backup.
     *
     * @param FileInterface $file
     * @return BackupInterface $this Fluent interface.
     */
    public function addFile(FileInterface $file);

    /**
     * Get backup files.
     *
     * @return FileInterface[] Backup files
     */
    public function getFiles();

    /**
     * Set backup files.
     *
     * @param FileInterface[] $files Files to backup.
     * @return BackupInterface $this Fluent interface.
     */
    public function setFiles(array $files);

    /**
     * Get backup total file size.
     *
     * @return int
     */
    public function getSize();

    /**
     * Set backup total file size.
     *
     * @param int $size Total file size of all backup files.
     * @return BackupInterface $this Fluent interface.
     */
    public function setSize($size);

    /**
     * Get backup creation date.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt();

    /**
     * Set backup creation date
     *
     * @param \DateTimeInterface|int $createdAt Backup creation date.
     * @return BackupInterface $this Fluent interface.
     */
    public function setCreatedAt($createdAt);

    /**
     * Get backup modification date.
     *
     * @return \DateTimeInterface
     */
    public function getModifiedAt();

    /**
     * Set backup modification date
     *
     * @param \DateTimeInterface|int $modifiedAt Backup modification date.
     * @return BackupInterface $this Fluent interface.
     */
    public function setModifiedAt($modifiedAt);
}

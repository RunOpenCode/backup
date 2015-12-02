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
use RunOpenCode\Backup\Exception\SourceException;

/**
 * Interface SourceInterface
 *
 * Represents abstract source (filesystem, database, etc.) of resources which ought to be backed up.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface SourceInterface
{
    /**
     * Fetch source for backup process.
     *
     * @return FileInterface[] Returns list of backup files for backup.
     * @throws SourceException
     */
    public function fetch();
}
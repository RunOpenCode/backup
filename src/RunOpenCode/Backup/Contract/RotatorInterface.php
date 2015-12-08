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
 * Interface RotatorInterface
 *
 * Rotator decides weather some existing backup should be removed from the backup storage.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface RotatorInterface
{
    /**
     * Nominates existing backups for removal from backup storage by consulting implemented decision strategy.
     *
     * @param BackupInterface[] $backups List of existing backups.
     * @return BackupInterface[] List of backups that should be removed from backup storage.
     */
    public function nominate(array $backups);
}

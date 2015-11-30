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
namespace RunOpenCode\Backup\Rotator;

use RunOpenCode\Backup\Contract\RotatorInterface;

/**
 * Class NullRotator
 *
 * Null rotator is default rotator implementation which keeps all backups, always.
 *
 * @package RunOpenCode\Backup\Rotator
 */
class NullRotator implements RotatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function nominate(array $backups)
    {
       return array();
    }
}
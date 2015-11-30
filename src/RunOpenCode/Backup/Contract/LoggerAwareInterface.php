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

use Psr\Log\LoggerAwareInterface as BaseLoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface LoggerAwareInterface
 *
 * Denotes a class that is aware of Logger.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface LoggerAwareInterface extends BaseLoggerAwareInterface
{
    /**
     * Get logger.
     *
     * @return LoggerInterface
     */
    public function getLogger();
}
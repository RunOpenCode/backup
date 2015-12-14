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
namespace RunOpenCode\Backup\Log;

use Psr\Log\LoggerInterface;

/**
 * Class LoggerAwareTrait
 *
 * Implementation of \RunOpenCode\Backup\Contract\LoggerAwareInterface.
 *
 * @package RunOpenCode\Backup\Log
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Set logger.
     *
     * @param LoggerInterface $logger
     * @return LoggerInterface $this Fluent interface.;
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}

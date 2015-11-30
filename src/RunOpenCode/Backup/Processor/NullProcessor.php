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
namespace RunOpenCode\Backup\Processor;

use RunOpenCode\Backup\Contract\ProcessorInterface;

/**
 * Class NullProcessor
 *
 * Null processor does not process backups.
 *
 * @package RunOpenCode\Backup\Processor
 */
class NullProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(array $backups)
    {
        return $backups;
    }
}
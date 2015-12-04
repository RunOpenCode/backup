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

use RunOpenCode\Backup\Exception\ProcessorException;

/**
 * Interface ProcessorInterface
 *
 * Processor process backup files and prepares them for upload.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface ProcessorInterface
{
    /**
     * Process backups and preparing them for upload.
     *
     * @param FileInterface[] $files Backup files to process.
     * @return FileInterface[]
     * @throws ProcessorException
     */
    public function process(array $files);
}
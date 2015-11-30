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
namespace RunOpenCode\Backup\Source;

use RunOpenCode\Backup\Contract\SourceInterface;

/**
 * Class NullSource
 *
 * Null source does not provide backup files.
 *
 * @package RunOpenCode\Backup\Source
 */
class NullSource implements SourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        return array();
    }
}
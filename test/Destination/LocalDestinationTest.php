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
namespace RunOpenCode\Backup\Tests\Destination;

use RunOpenCode\Backup\Destination\LocalDestination;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LocalDestinationTest extends BaseConcreteDestinationTest
{
    /**
     * {@inheritdoc}
     */
    protected function getDestination()
    {
        return new LocalDestination($this->directory, $this->filesystem);
    }
}

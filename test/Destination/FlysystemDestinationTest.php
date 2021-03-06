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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RunOpenCode\Backup\Destination\FlysystemDestination;

class FlysystemDestinationTest extends BaseConcreteDestinationTest
{
    /**
     * {@inheritdoc}
     */
    protected function getDestination()
    {
        return new FlysystemDestination(new Filesystem(new Local($this->directory)));
    }
}
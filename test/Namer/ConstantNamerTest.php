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
namespace RunOpenCode\Backup\Tests\Namer;

use RunOpenCode\Backup\Namer\Constant;

class ConstantNamerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetsName()
    {
        $namer = new Constant('backup');
        $this->assertSame('backup', $namer->getName());
    }
}
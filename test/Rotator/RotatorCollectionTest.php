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
namespace RunOpenCode\Backup\Tests\Rotator;

use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Rotator\MaxCountRotator;
use RunOpenCode\Backup\Rotator\MaxSizeRotator;
use RunOpenCode\Backup\Rotator\RotatorCollection;

class RotatorCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function nominate()
    {
        $rotator = new RotatorCollection(array(
            new MaxCountRotator(3),
            new MaxSizeRotator(350)
        ));

        $nominations = $rotator->nominate(array(
            new Backup('test1', array(), 100, new \DateTime('2011-10-19'), new \DateTime('2011-10-19')),
            new Backup('test2', array(), 100, new \DateTime('2011-10-18'), new \DateTime('2011-10-18')),
            new Backup('test3', array(), 100, new \DateTime('2011-10-10'), new \DateTime('2011-10-10')),
            new Backup('test4', array(), 100, new \DateTime('2011-10-11'), new \DateTime('2011-10-11')),
            new Backup('test5', array(), 100, new \DateTime('2011-10-14'), new \DateTime('2011-10-14')),
            new Backup('test6', array(), 100, new \DateTime('2011-10-16'), new \DateTime('2011-10-16'))
        ));

        $this->assertArraySubset(array('test3', 'test4', 'test5'), array_map(function(Backup $backup) {
            return $backup->getName();
        }, $nominations));

        $this->assertEquals(3, count($nominations));
    }
}
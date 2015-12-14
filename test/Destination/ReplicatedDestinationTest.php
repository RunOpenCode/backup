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

use Psr\Log\NullLogger;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Destination\ReplicatedDestination;
use RunOpenCode\Backup\Destination\LocalDestination;
use RunOpenCode\Backup\Exception\DestinationException;

class ReplicatedDestinationTest extends BaseDestinationTest
{
    /**
     * @test
     */
    public function twoStreamDestinations()
    {
        $this->clearDestination();

        $master = new LocalDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1');
        $slave = new LocalDestination($this->directory . DIRECTORY_SEPARATOR . 'destination2');

        $replica = new ReplicatedDestination($master, $slave);

        $files = $this->fetchSomeFiles();

        $replica->push(new Backup('test_backup', $files));

        foreach (array(
                     $this->directory . DIRECTORY_SEPARATOR . 'destination1',
                     $this->directory . DIRECTORY_SEPARATOR . 'destination2'
                 ) as $directory) {
            $cleanDestination = new LocalDestination($directory);

            $this->assertTrue($cleanDestination->has('test_backup'), 'Each destination has backup.');
            $this->assertSame(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Each backup has same files.');
        }
    }

    /**
     * @test
     */
    public function slaveFailWillNotFailBackup()
    {
        $this->clearDestination();

        $master = new LocalDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1');

        $slave = $this->getMockBuilder('RunOpenCode\\Backup\\Contract\\DestinationInterface')->getMock();
        $slave->method('push')->willThrowException(new DestinationException());

        $replica = new ReplicatedDestination($master, $slave);

        $files = $this->fetchSomeFiles();

        $replica->push(new Backup('test_backup', $files));

        $cleanDestination = new LocalDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1');
        $this->assertTrue($cleanDestination->has('test_backup'), 'Master has backup.');
        $this->assertSame(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Master has same files.');

    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\DestinationException
     */
    public function slaveFailsAllFails()
    {
        $this->clearDestination();

        $master = new LocalDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1');

        $slave = $this->getMockBuilder('RunOpenCode\\Backup\\Contract\\DestinationInterface')->getMock();
        $slave->method('push')->willThrowException(new DestinationException());

        $replica = new ReplicatedDestination($master, $slave, true);

        $replica->push(new Backup('test_backup', $this->fetchSomeFiles()));
    }
}

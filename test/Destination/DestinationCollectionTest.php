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

use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Destination\DestinationCollection;
use RunOpenCode\Backup\Destination\StreamDestination;
use RunOpenCode\Backup\Exception\DestinationException;
use Symfony\Component\Filesystem\Filesystem;

class DestinationCollectionTest extends BaseStreamDestinationTest
{
    /**
     * @test
     */
    public function twoStreamDestinations()
    {
        $this->clearDestination();

        $destination1 = new StreamDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1');
        $destination2 = new StreamDestination($this->directory . DIRECTORY_SEPARATOR . 'destination2');

        $theCollection = new DestinationCollection();

        $theCollection
            ->addDestination($destination1)
            ->addDestination($destination2);

        $files = $this->fetchSomeFiles();

        $theCollection->push(new Backup('test_backup', $files));

        foreach (array(
                     $this->directory . DIRECTORY_SEPARATOR . 'destination1',
                     $this->directory . DIRECTORY_SEPARATOR . 'destination2'
                 ) as $directory) {
            $cleanDestination = new StreamDestination($directory);

            $this->assertTrue($cleanDestination->has('test_backup'), 'Each destination has backup.');
            $this->assertSame(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Each backup has same files.');
        }
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\DestinationException
     */
    public function oneFailsAllFails()
    {
        $this->clearDestination();

        $stub = $this->getMockBuilder('RunOpenCode\\Backup\\Contract\\DestinationInterface')->getMock();
        $stub->method('push')->willThrowException(new DestinationException());

        $theCollection = new DestinationCollection(array(
            new StreamDestination($this->directory . DIRECTORY_SEPARATOR . 'destination1'),
            $stub
        ));

        $theCollection->push(new Backup('test_backup', $this->fetchSomeFiles()));
    }
}

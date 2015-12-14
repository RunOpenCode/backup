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
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\DestinationInterface;

abstract class BaseConcreteDestinationTest extends BaseDestinationTest
{
    /**
     * @test
     */
    public function pushNew()
    {
        $this->clearDestination();

        $files = $this->fetchSomeFiles();
        $testDestination = $this->getDestination();
        $controlDestination = $this->getDestination();

        $testDestination->push(new Backup('test_backup', $files));

        $this->assertTrue($controlDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(count($files), count($controlDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');

        $this->assertSame(array_map(function($file) {
            return array(
                'relative_path' => $file->getRelativePath(),
                'size' => $file->getSize()
            );
        }, $files), array_map(function($file) {
            return array(
                'relative_path' => $file->getRelativePath(),
                'size' => $file->getSize()
            );
        }, array_values($controlDestination->get('test_backup')->getFiles())), 'Source and destination contain same files.');
    }

    /**
     * @test
     */
    public function incrementalBackup()
    {
        $this->clearDestination();

        $files = $this->fetchSomeFiles();
        $testDestination = $this->getDestination();
        $controlDestination = $this->getDestination();

        $tmpFile = tempnam(sys_get_temp_dir(), 'some_file.txt');
        file_put_contents($tmpFile, 'some data');

        $testDestination->push(new Backup('test_backup', array(
            $files[0],
            File::fromLocal($tmpFile)
        )));

        $this->assertTrue($controlDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(2, count($controlDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');

        $testDestination->push(new Backup('test_backup', $files));

        $controlDestination = $this->getDestination();

        $this->assertTrue($controlDestination->has('test_backup'), 'Destination has same backup.');
        $this->assertEquals(1, $controlDestination->count(), 'Destination has only one backup.');
        $this->assertEquals(count($files), count($controlDestination->get('test_backup')->getFiles()), 'Destination has 2 new files of source, one old, one file is removed.');
        $this->assertFalse(in_array(basename($tmpFile), array_map(function($item) {
            return $item->getRelativePath();
        }, $controlDestination->get('test_backup')->getFiles())), 'Removed file is file created in tmp dir.');

    }

    /**
     * @return DestinationInterface
     */
    abstract protected function getDestination();
}
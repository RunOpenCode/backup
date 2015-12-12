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
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Destination\FlysystemDestination;

class FlysystemDestinationTest extends BaseStreamDestinationTest
{
    /**
     * @var FlysystemDestination
     */
    protected $destination;

    public function setUp()
    {
        parent::setUp();
        $this->destination = new FlysystemDestination(new Filesystem(new Local($this->directory)));
    }

    /**
     * @test
     */
    public function pushNew()
    {
        $this->clearDestination();

        $files = $this->fetchSomeFiles();

        $this->destination->push(new Backup('test_backup', $files));

        $cleanDestination = new FlysystemDestination(new Filesystem(new Local($this->directory)));

        $this->assertTrue($cleanDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');

        $this->assertSame(array_map(function($file) {
            return array(
                'name' => $file->getName(),
                'relative_path' => $file->getRelativePath(),
                'size' => $file->getSize()
            );
        }, $files), array_map(function($file) {
            return array(
                'name' => $file->getName(),
                'relative_path' => $file->getRelativePath(),
                'size' => $file->getSize()
            );
        }, array_values($cleanDestination->get('test_backup')->getFiles())), 'Source and destination contain same files.');
    }

    /**
     * @test
     */
    public function incrementalBackup()
    {
        $this->clearDestination();

        $files = $this->fetchSomeFiles();
        $tmpFile = tempnam(sys_get_temp_dir(), 'some_file.txt');
        file_put_contents($tmpFile, 'some data');

        $this->destination->push(new Backup('test_backup', array(
            $files[0],
            File::fromLocal($tmpFile)
        )));

        $cleanDestination = new FlysystemDestination(new Filesystem(new Local($this->directory)));
        $this->assertTrue($cleanDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(2, count($cleanDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');

        $this->destination->push(new Backup('test_backup', $files));

        $cleanDestination = new FlysystemDestination(new Filesystem(new Local($this->directory)));
        $this->assertTrue($cleanDestination->has('test_backup'), 'Destination has same backup.');
        $this->assertEquals(1, $cleanDestination->count(), 'Destination has only one backup.');
        $this->assertEquals(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Destination has 2 new files of source, one old, one file is removed.');
        $this->assertFalse(in_array(basename($tmpFile), array_map(function($item) {
            return $item->getName();
        }, $cleanDestination->get('test_backup')->getFiles())), 'Removed file is file created in tmp dir.');

    }
}
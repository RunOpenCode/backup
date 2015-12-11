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
namespace RunOpenCode\Backup\Tests\Source;

use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\BackupInterface;
use RunOpenCode\Backup\Destination\StreamDestination;
use RunOpenCode\Backup\Source\GlobSource;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class StreamDestinationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamDestination
     */
    protected $destination;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'roc_backup_destination';
        $this->destination = new StreamDestination($this->directory, $this->filesystem);
    }

    /**
     * @test
     */
    public function pushNew()
    {
        $this->clearDestination();

        $files = $this->fetchSomeFiles();

        $this->destination->push(new Backup('test_backup', $files));

        $cleanDestination = new StreamDestination($this->directory, $this->filesystem);

        $this->assertTrue($cleanDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(count($files), count($cleanDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');

        $this->assertSame(array_map(function($file) {
            return array(
                'name' => $file->getName(),
                'relative_path' => $file->getRelativePath(),
                'modified_time' => $file->getModifiedAt()->format('Y-m-d H:i:s'),
                'size' => $file->getSize()
            );
        }, $files), array_map(function($file) {
            return array(
                'name' => $file->getName(),
                'relative_path' => $file->getRelativePath(),
                'modified_time' => $file->getModifiedAt()->format('Y-m-d H:i:s'),
                'size' => $file->getSize()
            );
        }, $cleanDestination->get('test_backup')->getFiles()), 'Source and destination contain same files.');
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

        $cleanDestination = new StreamDestination($this->directory, $this->filesystem);

        $this->assertTrue($cleanDestination->has('test_backup'), 'Destination has a backup.');
        $this->assertEquals(2, count($cleanDestination->get('test_backup')->getFiles()), 'Destination has same number of files as source.');
    }

    public function tearDown()
    {
        $this->clearDestination();
    }

    protected function clearDestination()
    {
        $this->filesystem->remove(Finder::create()->in($this->directory));
        return $this;
    }

    /**
     * @return \RunOpenCode\Backup\Contract\FileInterface[]
     */
    protected function fetchSomeFiles()
    {
        $source = new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*');
        return $source->fetch();
    }
}
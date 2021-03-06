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
namespace RunOpenCode\Backup\Tests\Processor;

use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Processor\ZipArchiveProcessor;
use RunOpenCode\Backup\Source\GlobSource;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ZipArchiveProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function zipAndCleanUp()
    {
        $source = new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*');
        $files = $source->fetch();

        $this->assertSame(3, count($files), 'There are 3 files to archive.');

        $processor = new ZipArchiveProcessor('archive.zip');
        $processor->setEventDispatcher($eventDispatcher = new EventDispatcher());

        $processedFiles = $processor->process($files);

        $this->assertSame(1, count($processedFiles), 'There is one compressed file');

        /**
         * @var FileInterface $processedFile
         */
        $processedFile = $processedFiles[0];

        $this->assertTrue(file_exists($processedFile->getPath()), 'Zip archive exists.');

        $eventDispatcher->dispatch(BackupEvents::TERMINATE, new BackupEvent());

        $this->assertFalse(file_exists($processedFile->getPath()), 'Zip archive is cleaned up.');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\ProcessorException
     */
    public function couldNotProcessEmptyCollection()
    {
        $processor = new ZipArchiveProcessor('archive.zip');
        $processor->setEventDispatcher($eventDispatcher = new EventDispatcher());

        $processor->process(array());
    }

}
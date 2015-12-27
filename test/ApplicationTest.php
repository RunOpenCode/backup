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
namespace RunOpenCode\Backup\Tests;

use Psr\Log\NullLogger;
use RunOpenCode\Backup\Backup\Profile;
use RunOpenCode\Backup\Destination\LocalDestination;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Manager;
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\ZipArchiveProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\GlobSource;
use RunOpenCode\Backup\Workflow\Workflow;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function backup()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $testResults = array();


        $eventDispatcher->addListener(BackupEvents::BEGIN, function(BackupEvent $event, $eventName) use (&$testResults) {
            $testResults[] = array(
                'expected' => BackupEvents::BEGIN,
                'actual' => $eventName,
                'message' => 'Begin event should be dispatched'
            );
        });

        $eventDispatcher->addListener(BackupEvents::FETCH, function(BackupEvent $event, $eventName) use (&$testResults) {

            $testResults[] = array(
                'expected' => BackupEvents::FETCH,
                'actual' => $eventName,
                'message' => 'Fetch event should be dispatched'
            );

            $testResults[] = array(
                'expected' => 'my-profile',
                'actual' => $event->getBackup()->getName(),
                'message' => 'Backup name is profile name'
            );
        });

        $eventDispatcher->addListener(BackupEvents::PROCESS, function(BackupEvent $event, $eventName) use (&$testResults) {

            $testResults[] = array(
                'expected' => BackupEvents::PROCESS,
                'actual' => $eventName,
                'message' => 'Process event should be dispatched'
            );

            $testResults[] = array(
                'expected' => 1,
                'actual' => count($event->getBackup()->getFiles()),
                'message' => 'Should contain only one file'
            );

            $testResults[] = array(
                'expected' => 'archive.zip',
                'actual' => $event->getBackup()->getFiles()[0]->getRelativePath(),
                'message' => 'Should contain zip file with given name'
            );
        });

        $eventDispatcher->addListener(BackupEvents::NAME, function(BackupEvent $event, $eventName) use (&$testResults) {

            $testResults[] = array(
                'expected' => BackupEvents::NAME,
                'actual' => $eventName,
                'message' => 'Name event should be dispatched'
            );

            $testResults[] = array(
                'expected' => 'backup-application-test',
                'actual' => $event->getBackup()->getName(),
                'message' => 'Backup name is given name'
            );
        });

        $eventDispatcher->addListener(BackupEvents::PRE_ROTATE, function(BackupEvent $event, $eventName) use (&$testResults) {
            $testResults[] = array(
                'expected' => BackupEvents::PRE_ROTATE,
                'actual' => $eventName,
                'message' => 'Pre-rotate event should be dispatched'
            );
        });

        $eventDispatcher->addListener(BackupEvents::POST_ROTATE, function(BackupEvent $event, $eventName) use (&$testResults) {
            $testResults[] = array(
                'expected' => BackupEvents::POST_ROTATE,
                'actual' => $eventName,
                'message' => 'Post-rotate event should be dispatched'
            );
        });

        $eventDispatcher->addListener(BackupEvents::PUSH, function(BackupEvent $event, $eventName) use (&$testResults) {

            $testResults[] = array(
                'expected' => BackupEvents::PUSH,
                'actual' => $eventName,
                'message' => 'Push event should be dispatched'
            );

            $testResults[] = array(
                'expected' => true,
                'actual' => $event->getProfile()->getDestination()->has('backup-application-test'),
                'message' => 'Backup is pushed into destination'
            );
        });

        $eventDispatcher->addListener(BackupEvents::TERMINATE, function(BackupEvent $event, $eventName) use (&$testResults) {
            $testResults[] = array(
                'expected' => BackupEvents::TERMINATE,
                'actual' => $eventName,
                'message' => 'Terminate event should be dispatched'
            );
        });

        $source = new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*');

        $processor = new ZipArchiveProcessor('archive.zip');
        $processor->setEventDispatcher($eventDispatcher);

        $namer = new Constant('backup-application-test');

        $rotator = new NullRotator();

        $destinationDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'roc_application_test';
        $filesystem = new Filesystem();
        $filesystem->remove($destinationDirectory);
        $destination = new LocalDestination($destinationDirectory);


        $profile = new Profile('my-profile', $source, $processor, $namer, $rotator, $destination, $rotator);

        $workflow = Workflow::build();
        $workflow->setEventDispatcher($eventDispatcher);
        $workflow->setLogger($logger);

        $manager = new Manager($workflow, array($profile));

        $manager->execute('my-profile');

        foreach ($testResults as $testResult) {
            $this->assertSame($testResult['expected'], $testResult['actual'], $testResult['message']);
        }
    }
}

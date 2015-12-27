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
namespace RunOpenCode\Backup\Tests\Workflow;

use Psr\Log\NullLogger;
use RunOpenCode\Backup\Backup\Backup;
use RunOpenCode\Backup\Backup\Profile;
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Destination\NullDestination;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\NullProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\GlobSource;
use RunOpenCode\Backup\Source\NullSource;
use RunOpenCode\Backup\Workflow\Fetch;
use RunOpenCode\Backup\Workflow\Workflow;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FetchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function fetchSuccess()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $fetchActivity = new Fetch();
        $profile = new Profile(
            'test',
            new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*'),
            new NullProcessor(),
            new Constant('test'),
            new NullRotator(),
            new NullDestination(),
            new NullRotator(),
            Workflow::build($eventDispatcher, $logger)
        );

        $fetchActivity->setLogger($logger);
        $fetchActivity->setEventDispatcher($eventDispatcher);
        $fetchActivity->setProfile($profile);
        $fetchActivity->setBackup(new Backup('test'));

        $listener = function(BackupEvent $event) {

            $this->assertEquals('test', $event->getBackup()->getName(), 'Expected backup arrived with the event.');

            $this->assertArraySubset(
                array('file1.txt', 'file2.txt', 'file3.txt'),
                array_map(function(FileInterface $file) {
                    return $file->getRelativePath();
                }, $event->getBackup()->getFiles()),
                false,
                'Has to have 3 specific files.'
            );
        };

        \Closure::bind($listener, $this);

        $eventDispatcher->addListener(BackupEvents::FETCH, $listener);

        $fetchActivity->execute();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\EmptySourceException
     */
    public function emptySourceException()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $fetchActivity = new Fetch();
        $profile = new Profile(
            'test',
            new NullSource(),
            new NullProcessor(),
            new Constant('test'),
            new NullRotator(),
            new NullDestination(),
            new NullRotator(),
            Workflow::build($eventDispatcher, $logger)
        );

        $fetchActivity->setLogger($logger);
        $fetchActivity->setEventDispatcher($eventDispatcher);
        $fetchActivity->setProfile($profile);
        $fetchActivity->setBackup(new Backup('test'));

        $fetchActivity->execute();
    }
}

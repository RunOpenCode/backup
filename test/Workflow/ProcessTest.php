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
use RunOpenCode\Backup\Destination\NullDestination;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Exception\ProcessorException;
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\NullProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\NullSource;
use RunOpenCode\Backup\Workflow\Process;
use RunOpenCode\Backup\Workflow\WorkflowFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function processSuccess()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $processActivity = new Process();
        $profile = new Profile(
            'test',
            new NullSource(),
            new NullProcessor(),
            new Constant(),
            new NullRotator(),
            new NullDestination(),
            new NullRotator(),
            WorkflowFactory::build($eventDispatcher, $logger)
        );

        $processActivity->setLogger($logger);
        $processActivity->setEventDispatcher($eventDispatcher);
        $processActivity->setProfile($profile);
        $processActivity->setBackup(new Backup('test'));

        $listener = function(BackupEvent $event) {

            $this->assertEquals('test', $event->getBackup()->getName(), 'Expected backup arrived with the event.');
        };

        \Closure::bind($listener, $this);

        $eventDispatcher->addListener(BackupEvents::PROCESS, $listener);

        $processActivity->execute();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\ProcessorException
     */
    public function processException()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $stub = $this->getMockBuilder('RunOpenCode\\Backup\\Processor\\NullProcessor')->getMock();
        $stub->method('process')->willThrowException(new ProcessorException());

        $processActivity = new Process();
        $profile = new Profile(
            'test',
            new NullSource(),
            $stub,
            new Constant(),
            new NullRotator(),
            new NullDestination(),
            new NullRotator(),
            WorkflowFactory::build($eventDispatcher, $logger)
        );

        $processActivity->setLogger($logger);
        $processActivity->setEventDispatcher($eventDispatcher);
        $processActivity->setProfile($profile);
        $processActivity->setBackup(new Backup('test'));

        $processActivity->execute();
    }
}

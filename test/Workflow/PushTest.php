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
use RunOpenCode\Backup\Exception\DestinationException;
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\NullProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\NullSource;
use RunOpenCode\Backup\Workflow\Push;
use RunOpenCode\Backup\Workflow\WorkflowFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PushTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function pushSuccess()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $pushActivity = new Push();
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

        $pushActivity->setLogger($logger);
        $pushActivity->setEventDispatcher($eventDispatcher);
        $pushActivity->setProfile($profile);
        $pushActivity->setBackup(new Backup('test'));

        $listener = function(BackupEvent $event) {

            $this->assertEquals('test', $event->getBackup()->getName(), 'Expected backup arrived with the event.');
        };

        \Closure::bind($listener, $this);

        $eventDispatcher->addListener(BackupEvents::PUSH, $listener);

        $pushActivity->execute();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\DestinationException
     */
    public function pushDestinationException()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $destinationStub = $this->getMockBuilder('RunOpenCode\\Backup\\Destination\\NullDestination')->getMock();
        $destinationStub
            ->method('push')
            ->willThrowException(new DestinationException());
        $destinationStub
            ->method('all')
            ->willReturn(array());

        $pushActivity = new Push();
        $profile = new Profile(
            'test',
            new NullSource(),
            new NullProcessor(),
            new Constant(),
            new NullRotator(),
            $destinationStub,
            new NullRotator(),
            WorkflowFactory::build($eventDispatcher, $logger)
        );

        $pushActivity->setLogger($logger);
        $pushActivity->setEventDispatcher($eventDispatcher);
        $pushActivity->setProfile($profile);
        $pushActivity->setBackup(new Backup('test'));

        $pushActivity->execute();
    }
}

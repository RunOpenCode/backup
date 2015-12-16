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
use RunOpenCode\Backup\Workflow\PreRotate;
use RunOpenCode\Backup\Workflow\WorkflowFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PreRotateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function preRotateSuccess()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $preRotateActivity = new PreRotate();
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

        $preRotateActivity->setLogger($logger);
        $preRotateActivity->setEventDispatcher($eventDispatcher);
        $preRotateActivity->setProfile($profile);
        $preRotateActivity->setBackup(new Backup('test'));

        $listener = function(BackupEvent $event) {

            $this->assertEquals('test', $event->getBackup()->getName(), 'Expected backup arrived with the event.');
        };

        \Closure::bind($listener, $this);

        $eventDispatcher->addListener(BackupEvents::PRE_ROTATE, $listener);

        $preRotateActivity->execute();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\DestinationException
     */
    public function rotateSourceException()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $destinationStub = $this->getMockBuilder('RunOpenCode\\Backup\\Destination\\NullDestination')->getMock();
        $destinationStub
            ->method('delete')
            ->willThrowException(new DestinationException());
        $destinationStub
            ->method('all')
            ->willReturn(array());

        $rotatorStub = $this->getMockBuilder('RunOpenCode\\Backup\\Rotator\\NullRotator')->getMock();
        $rotatorStub->method('nominate')->willReturn(array(new Backup('test')));

        $rotateActivity = new PreRotate();
        $profile = new Profile(
            'test',
            new NullSource(),
            new NullProcessor(),
            new Constant(),
            $rotatorStub,
            $destinationStub,
            new NullRotator(),
            WorkflowFactory::build($eventDispatcher, $logger)
        );

        $rotateActivity->setLogger($logger);
        $rotateActivity->setEventDispatcher($eventDispatcher);
        $rotateActivity->setProfile($profile);
        $rotateActivity->setBackup(new Backup('test'));

        $rotateActivity->execute();
    }
}

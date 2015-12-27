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
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\NullProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\NullSource;
use RunOpenCode\Backup\Workflow\Name;
use RunOpenCode\Backup\Workflow\Workflow;
use Symfony\Component\EventDispatcher\EventDispatcher;

class NameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function nameSuccess()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $nameActivity = new Name();
        $profile = new Profile(
            'test',
            new NullSource(),
            new NullProcessor(),
            new Constant('new_name'),
            new NullRotator(),
            new NullDestination(),
            new NullRotator(),
            Workflow::build($eventDispatcher, $logger)
        );

        $nameActivity->setLogger($logger);
        $nameActivity->setEventDispatcher($eventDispatcher);
        $nameActivity->setProfile($profile);
        $nameActivity->setBackup(new Backup('test'));

        $listener = function(BackupEvent $event) {

            $this->assertEquals('new_name', $event->getBackup()->getName(), 'Expected backup arrived with the event.');
        };

        \Closure::bind($listener, $this);

        $eventDispatcher->addListener(BackupEvents::NAME, $listener);

        $nameActivity->execute();
    }
}

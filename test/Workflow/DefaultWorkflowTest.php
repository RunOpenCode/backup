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
use RunOpenCode\Backup\Backup\Profile;
use RunOpenCode\Backup\Destination\NullDestination;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Exception\SourceException;
use RunOpenCode\Backup\Namer\Constant;
use RunOpenCode\Backup\Processor\NullProcessor;
use RunOpenCode\Backup\Rotator\NullRotator;
use RunOpenCode\Backup\Source\GlobSource;
use RunOpenCode\Backup\Workflow\Workflow;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DefaultWorkflowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function defaultWorkflow()
    {
        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $profile = new Profile(
            'test',
            new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*'),
            new NullProcessor(),
            new Constant(),
            new NullRotator(),
            new NullDestination(),
            new NullRotator()
        );

        $eventStack = array_reverse(array(
            BackupEvents::BEGIN,
            BackupEvents::FETCH,
            BackupEvents::PROCESS,
            BackupEvents::NAME,
            BackupEvents::PRE_ROTATE,
            BackupEvents::PUSH,
            BackupEvents::POST_ROTATE,
            BackupEvents::TERMINATE
        ));

        $testCases = array();

        foreach ($eventStack as $eventName) {

            $eventDispatcher->addListener($eventName,  function(BackupEvent $event, $eventName) use (&$eventStack, &$testCases) {

                $expected = array_pop($eventStack);

                $testCases[] = array(
                    'expected' => $expected,
                    'actual' => $eventName,
                    'message' => sprintf('Expected backup event "%s" triggered.', $expected)
                );
            });
        }

        $workflow = Workflow::build();

        $workflow->setLogger($logger);
        $workflow->setEventDispatcher($eventDispatcher);

        $workflow->execute($profile);

        foreach ($testCases as $testCase) {
            $this->assertSame($testCase['expected'], $testCase['actual'], $testCase['message']);
        }
    }

    /**
     * @test
     */
    public function workflowErrorEvent()
    {

        $logger = new NullLogger();
        $eventDispatcher = new EventDispatcher();

        $sourceStub = $this->getMockBuilder('RunOpenCode\\Backup\\Source\\NullSource')->getMock();
        $sourceStub
            ->method('fetch')
            ->willThrowException(new SourceException());

        $profile = new Profile(
            'test',
            $sourceStub,
            new NullProcessor(),
            new Constant(),
            new NullRotator(),
            new NullDestination(),
            new NullRotator()
        );

        $errorTriggered = false;

        $eventDispatcher->addListener(BackupEvents::ERROR, function(BackupEvent $event, $eventName) use (&$errorTriggered) {
            $errorTriggered = true;
        });

        $workflow = Workflow::build();

        $workflow->setLogger($logger);
        $workflow->setEventDispatcher($eventDispatcher);

        try {
            $workflow->execute($profile);
        } catch (SourceException $e) {
            // noop
        }

        $this->assertTrue($errorTriggered);
    }
}

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

use Psr\Log\NullLogger;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Source\MySqlDumpSource;
use Symfony\Component\EventDispatcher\EventDispatcher;

class MySqlDumpSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function successfulDumpAndCleanup()
    {
        $settings = require_once file_exists($config = __DIR__ .'/../Fixtures/config/mysqldump.php') ? $config : __DIR__ .'/../Fixtures/config/mysqldump.dist.php';
        $source = new MySqlDumpSource($settings['database'], $settings['username'], $settings['password'], $settings['host'], $settings['port']);

        $source->setEventDispatcher($eventDispatcher = new EventDispatcher());

        $files = $source->fetch();

        $this->assertSame(1, count($files), 'It should dump one mysql file.');

        $this->assertTrue(file_exists($files[0]->getPath()), 'That file should exist prior to termination of backup process.');

        $eventDispatcher->dispatch(BackupEvents::TERMINATE, new BackupEvent());

        $this->assertFalse(file_exists($files[0]->getPath()), 'That file should not exist after termination of backup process.');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\SourceException
     */
    public function connectionError()
    {
        $settings = require_once file_exists($config = __DIR__ .'/../Fixtures/config/mysqldump.php') ? $config : __DIR__ .'/../Fixtures/config/mysqldump.dist.php';
        $source = new MySqlDumpSource($settings['database'], $settings['username'], $settings['password'], 'www.non-existing-domain.com', $settings['port']);

        $source->setEventDispatcher($eventDispatcher = new EventDispatcher());

        $source->fetch();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\SourceException
     */
    public function databaseError()
    {
        $settings = require_once file_exists($config = __DIR__ .'/../Fixtures/config/mysqldump.php') ? $config : __DIR__ .'/../Fixtures/config/mysqldump.dist.php';
        $source = new MySqlDumpSource('There is no way that you have database with this name.', $settings['username'], $settings['password'], $settings['host'], $settings['port']);

        $source->setEventDispatcher($eventDispatcher = new EventDispatcher());

        $source->fetch();
    }
}
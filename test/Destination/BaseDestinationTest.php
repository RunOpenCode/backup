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
namespace RunOpenCode\Backup\Tests\Destination;

use RunOpenCode\Backup\Source\GlobSource;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class BaseDestinationTest extends \PHPUnit_Framework_TestCase
{
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
    }

    public function tearDown()
    {
        $this->clearDestination();
    }

    protected function clearDestination()
    {
        try {
            $this->filesystem->remove(Finder::create()->in($this->directory));
        } catch (\Exception $e) {
            // noop
        }

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

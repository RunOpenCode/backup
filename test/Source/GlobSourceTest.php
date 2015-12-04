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
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Source\GlobSource;

class GlobSourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function singleGlob()
    {
        $source = new GlobSource(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*');
        $files = $source->fetch();

        $this->assertArraySubset(
            array('file1.txt', 'file2.txt', 'file3.txt'),
            array_map(function(FileInterface $file) {
                return $file->getName();
            }, $files),
            false,
            'Has to have 3 specific files.'
        );

        $this->assertArraySubset(
            array('file1.txt', 'file2.txt', 'file3.txt'),
            array_map(function(FileInterface $file) {
                return $file->getRelativePath();
            }, $files),
            false,
            'Relative path must be filename since glob is root path.'
        );
    }

    /**
     * @test
     */
    public function multipleGlobs()
    {
        $source = new GlobSource(array(
            realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*' => realpath(__DIR__ . '/../Fixtures/glob'),
            realpath(__DIR__ . '/../Fixtures/glob/globSet2') . '/*' => realpath(__DIR__ . '/../Fixtures/glob'),
        ));

        $files = $source->fetch();

        $this->assertArraySubset(
            array('file1.txt', 'file2.txt', 'file3.txt', 'file4.txt', 'file5.txt', 'file6.txt'),
            array_map(function(FileInterface $file) {
                return $file->getName();
            }, $files),
            false,
            'Has to have 6 specific files.'
        );

        $this->assertArraySubset(
            array('globSet1/file1.txt', 'globSet1/file2.txt', 'globSet1/file3.txt', 'globSet2/file4.txt', 'globSet2/file5.txt', 'globSet2/file6.txt'),
            array_map(function(FileInterface $file) {
                return $file->getRelativePath();
            }, $files),
            false,
            'Relative path must be as define since glob root path is given.'
        );
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\SourceException
     */
    public function invalidGlob()
    {
        $source = new GlobSource('/**/*.(txt)');
        $source->fetch();
    }
}
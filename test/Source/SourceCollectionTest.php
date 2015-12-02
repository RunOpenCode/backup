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
use RunOpenCode\Backup\Source\Glob;
use RunOpenCode\Backup\Source\SourceCollection;

class SourceCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sourceCollectionCombinesResultsFromSeveralSources()
    {
        $source = new SourceCollection();

        $source
            ->add($src1 = new Glob(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*'))
            ->add($src2 = new Glob(realpath(__DIR__ . '/../Fixtures/glob/globSet2') . '/*'));

        $logger = new NullLogger();

        $src1->setLogger($logger);
        $src2->setLogger($logger);

        $files = $source->fetch();

        $this->assertSame(6, count($files), 'Collection returns all files from all sources.');

        $this->assertArraySubset(
            array('file1.txt', 'file2.txt', 'file3.txt', 'file4.txt', 'file5.txt', 'file6.txt'),
            array_map(function(FileInterface $file) {
                return $file->getName();
            }, $files),
            false,
            'Has to have 6 specific files.'
        );
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Backup\Exception\SourceException
     */
    public function ifOneSourceFailsWholeCollectionFails()
    {
        $source = new SourceCollection();

        $directory = realpath(__DIR__ . '/../Fixtures/glob/globCanNotReadThis');

        @chmod($directory, 0200);

        $source
            ->add($src1 = new Glob(realpath(__DIR__ . '/../Fixtures/glob/globSet1') . '/*'))
            ->add($src2 = new Glob($directory. '/*'));

        $logger = new NullLogger();

        $src1->setLogger($logger);
        $src2->setLogger($logger);

        $files = $source->fetch();
    }

}
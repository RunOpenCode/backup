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
namespace RunOpenCode\Backup\Tests\Processor;


use RunOpenCode\Backup\Processor\ProcessorCollection;

class ProcessorCollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function collectionOfProcessorIsExecuted()
    {
        $processor1 = $this->getMockBuilder('RunOpenCode\\Backup\\Processor\\NullProcessor')->getMock();
        $processor1
            ->method('process')
            ->willReturn(array(1 => 1));

        $processor2 = $this->getMockBuilder('RunOpenCode\\Backup\\Processor\\NullProcessor')->getMock();
        $processor2
            ->method('process')
            ->willReturn(array(2 => 2));

        $collection = new ProcessorCollection(array(
            $processor1,
            $processor2
        ));

        $this->assertArrayHasKey(2, $collection->process(array()));

        $collection = new ProcessorCollection(array(
            $processor2,
            $processor1
        ));

        $this->assertArrayHasKey(1, $collection->process(array()));
    }
}

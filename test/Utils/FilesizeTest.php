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
namespace RunOpenCode\Backup\Tests\Utils;

use RunOpenCode\Backup\Utils\Filesize;

class FilesizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function validSizes()
    {
        $data = array(
            '15m' => (15 * 8388608),
            '15mB' => (15 * 8388608),
            '15MB' => (15 * 8388608),
            '15M' => (15 * 8388608),
            '20G' => (20 * 8589934592),
            '20gb' => (20 * 8589934592),
            '20GB' => (20 * 8589934592),
            '20Gb' => (20 * 8589934592),
            '10T' => (10 * 8796093022208),
            '10Tb' => (10 * 8796093022208),
            '10tb' => (10 * 8796093022208),
            '10TB' => (10 * 8796093022208),
            100 => 100
        );

        foreach ($data as $input => $expectedOutput) {
            $this->assertSame($expectedOutput, Filesize::getBytes($input), 'Output size match');
        }
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function invalidSizeFormat()
    {
        Filesize::getBytes('15 M');
    }
}

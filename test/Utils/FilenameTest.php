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

use RunOpenCode\Backup\Utils\Filename;

class FilenameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sanitize()
    {
        $invalidChars = array(
            '/',
            '\\',
            '?',
            '%',
            '*',
            ':',
            ';',
            '|',
            '"',
            '<',
            '>',
        );

        foreach ($invalidChars as $invalidChar) {
            $this->assertFalse(strpos(
                Filename::sanitize('someFilename' . $invalidChar . 'Pattern'),
                $invalidChar
            ));
        }
    }

    /**
     * @test
     */
    public function temporaryFilename()
    {
        $filename = Filename::temporaryFilename('some_test_name');

        $this->assertFalse(file_exists($filename));
        $this->assertNotFalse(strpos($filename, 'some_test_name'));
    }

    /**
     * @test
     */
    public function temporaryFile()
    {
        $filename = Filename::temporaryFile('some_test_name');

        $this->assertTrue(file_exists($filename));
        $this->assertNotFalse(strpos($filename, 'some_test_name'));
    }
}

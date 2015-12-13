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
namespace RunOpenCode\Backup\Utils;

final class Filename
{
    private function __construct() { }

    /**
     * Sanitize filename.
     *
     * @param string $filename Filename to sanitize.
     * @return string Sanitized filename
     */
    public static function sanitize($filename)
    {
        if (function_exists('mb_ereg_replace')) {
            return mb_ereg_replace("([\.]{2,})", '', mb_ereg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $filename));
        } else {
            return preg_replace("/([\.]{2,})/", '', preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).])/", '', $filename));
        }
    }

    /**
     * Create unique temporary file with given filename in system's temporary directory.
     *
     * @param string $filename Filename of temporary file.
     * @return string Absolute path to created temporary file.
     */
    public static function temporaryFile($filename)
    {
        $temporaryFile = self::temporaryFilename($filename);

        if (touch($temporaryFile) === false) {
            throw new \RuntimeException(sprintf('Unable to create temporary file %s.', $filename));
        }

        return $temporaryFile;
    }

    /**
     * Create temporary filename with given filename with path in system's temporary directory.
     *
     * @param string $filename Filename of temporary file.
     * @return string Generated absolute path to unique temporary file.
     */
    public static function temporaryFilename($filename)
    {
        $tmp = tempnam(sys_get_temp_dir(), '');

        if (unlink($tmp) === false || mkdir($tmp) === false) {
            throw new \RuntimeException(sprintf('Unable to create temporary file %s.', $filename));
        }

        return $tmp . DIRECTORY_SEPARATOR . self::sanitize($filename);
    }
}

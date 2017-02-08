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
namespace RunOpenCode\Backup\Exception;

use RunOpenCode\Backup\Contract\ExceptionInterface;

class Exception extends \Exception implements ExceptionInterface
{
    /**
     * Get type of argument for exception messages.
     *
     * @param $arg
     * @return string
     */
    public static function typeOf($arg) {
        if (is_null($arg)) {
            return 'NULL';
        } elseif (is_object($arg)) {
            return get_class($arg);
        } else {
            return gettype($arg);
        }
    }
}

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

/**
 * Class Filesize
 *
 * File size utilities
 *
 * @package RunOpenCode\Backup\Utils
 */
final class Filesize
{
    private function __construct() {}

    private static $units = array(
        't' => 8796093022208,
        'tb' => 8796093022208,
        'g' => 8589934592,
        'gb' => 8589934592,
        'm' => 8388608,
        'mb' => 8388608
    );

    /**
     * Get bytes from formatted string size.
     *
     * E.g:
     *
     * 1m, 1mb => megabytes
     * 1g, 1gb => gigabytes
     * 1t, 1tb => terabytes
     *
     * Note: size format is case insensitive.
     *
     * @param $size
     * @return int
     */
    public static function getBytes($size)
    {
        if (is_numeric($size)) {
            return intval($size);
        }

        $size = strtolower(trim($size));

        foreach (self::$units as $unit => $bytes) {

            if (($temp = strlen($size) - strlen($unit)) >= 0 && strpos($size, $unit, $temp) !== false) {

                $numberPart = str_replace($unit, '', $size);

                if (is_numeric($numberPart) && $numberPart > 0) {
                    return $bytes * $numberPart;
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid size format: "%s"', $numberPart));
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown size format: "%s"', $size));
    }
}

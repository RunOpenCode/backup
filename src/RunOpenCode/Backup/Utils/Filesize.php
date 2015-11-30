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
abstract class Filesize
{
    private final function __construct() {}

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

        $units = array(
            't' => (pow(1024, 4) * 8),
            'g' => (pow(1024, 3) * 8),
            'm' => (pow(1024, 2) * 8),
            'tb' => (pow(1024, 4) * 8),
            'gb' => (pow(1024, 3) * 8),
            'mb' => (pow(1024, 2) * 8)
        );

        foreach ($units as $unit => $bytes) {
            if (($temp = strlen($size) - strlen($unit)) >= 0 && strpos($size, $unit, $temp) !== FALSE) {

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
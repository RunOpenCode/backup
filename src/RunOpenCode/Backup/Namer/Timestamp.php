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
namespace RunOpenCode\Backup\Namer;

use RunOpenCode\Backup\Contract\NamerInterface;

/**
 * Class Timestamp
 *
 * Namer that names backup with current timestamp.
 *
 * @package RunOpenCode\Backup\Namer
 */
final class Timestamp implements NamerInterface
{
    private $format;

    public function __construct($format = 'Y-m-d-H-i-s')
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return date($this->format);
    }
}
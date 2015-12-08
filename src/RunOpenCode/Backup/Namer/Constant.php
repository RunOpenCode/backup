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
 * Class Constant
 *
 * Namer that always names backup with same name.
 *
 * @package RunOpenCode\Backup\Namer
 */
final class Constant implements NamerInterface
{
    private $name;

    public function __construct($name = 'backup')
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}

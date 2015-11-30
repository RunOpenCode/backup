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
namespace RunOpenCode\Backup\Rotator;

use RunOpenCode\Backup\Contract\RotatorInterface;

/**
 * Class RotatorCollection
 *
 * Rotator collection consults several rotators for nomination.
 *
 * @package RunOpenCode\Backup\Rotator
 */
class RotatorCollection implements \IteratorAggregate, RotatorInterface
{
    /**
     * @var RotatorInterface[]
     */
    private $rotators;

    public function __construct(array $rotators = array())
    {
        $this->rotators = $rotators;
    }

    /**
     * {@inheritdoc}
     */
    public function nominate(array $backups)
    {
        $result = array();

        /** @var RotatorInterface $rotator */
        foreach ($this->rotators as $rotator) {

            foreach ($nominations = $rotator->nominate($backups) as $nomination) {

                if (!in_array($nomination, $result)) {
                    $result[] = $nomination;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rotators);
    }
}
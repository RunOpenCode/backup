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
namespace RunOpenCode\Backup\Backup;

use RunOpenCode\Backup\Contract\DestinationInterface;
use RunOpenCode\Backup\Contract\NamerInterface;
use RunOpenCode\Backup\Contract\ProcessorInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\RotatorInterface;
use RunOpenCode\Backup\Contract\SourceInterface;

/**
 * Class Profile
 *
 * Backup profile.
 *
 * @package RunOpenCode\Backup\Backup
 */
final class Profile implements ProfileInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var NamerInterface
     */
    private $namer;

    /**
     * @var RotatorInterface
     */
    private $preRotator;

    /**
     * @var DestinationInterface
     */
    private $destination;

    /**
     * @var RotatorInterface
     */
    private $postRotator;

    public function __construct(
        $name,
        SourceInterface $source,
        ProcessorInterface $processor,
        NamerInterface $namer,
        RotatorInterface $preRotator,
        DestinationInterface $destination,
        RotatorInterface $postRotator
    ) {
        $this->name = $name;
        $this->source = $source;
        $this->processor = $processor;
        $this->namer = $namer;
        $this->preRotator = $preRotator;
        $this->destination = $destination;
        $this->postRotator = $postRotator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return NamerInterface
     */
    public function getNamer()
    {
        return $this->namer;
    }

    /**
     * @return RotatorInterface
     */
    public function getPreRotator()
    {
        return $this->preRotator;
    }

    /**
     * @return DestinationInterface
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return RotatorInterface
     */
    public function getPostRotator()
    {
        return $this->postRotator;
    }
}
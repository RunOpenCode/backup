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
use RunOpenCode\Backup\Contract\WorkflowInterface;

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
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamer()
    {
        return $this->namer;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreRotator()
    {
        return $this->preRotator;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostRotator()
    {
        return $this->postRotator;
    }
}

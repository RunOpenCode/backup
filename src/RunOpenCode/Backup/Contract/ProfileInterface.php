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
namespace RunOpenCode\Backup\Contract;

/**
 * Interface ProfileInterface
 *
 * Profile defines backup profile.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface ProfileInterface
{
    /**
     * Get profile name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get source.
     *
     * @return SourceInterface
     */
    public function getSource();

    /**
     * Get processor.
     *
     * @return ProcessorInterface
     */
    public function getProcessor();

    /**
     * Get namer.
     *
     * @return NamerInterface
     */
    public function getNamer();

    /**
     * Get pre-rotator.
     *
     * @return RotatorInterface
     */
    public function getPreRotator();

    /**
     * Get destination.
     *
     * @return DestinationInterface
     */
    public function getDestination();

    /**
     * Get post-rotator.
     *
     * @return RotatorInterface
     */
    public function getPostRotator();

    /**
     * Get workflow for this profile.
     *
     * @return WorkflowInterface
     */
    public function getWorkflow();
}

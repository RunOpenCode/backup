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
 * Interface ManagerInterface
 *
 * Backup manager.
 *
 * @package RunOpenCode\Backup\Contract
 */
interface ManagerInterface extends \IteratorAggregate
{
    /**
     * Add profile to profiles collection.
     *
     * @param ProfileInterface $profile Profile to add.
     * @return ManagerInterface $this Fluent interface.
     */
    public function add(ProfileInterface $profile);

    /**
     * Check if profile exists in profiles collection.
     *
     * @param string $name Profile name.
     * @return bool TRUE if exits.
     */
    public function has($name);

    /**
     * Get profile with given name.
     *
     * @param string $name Profile name.
     * @return ProfileInterface
     */
    public function get($name);

    /**
     * Execute profile.
     *
     * @param string $name Profile name.
     * @return ManagerInterface $this Fluent interface.
     */
    public function execute($name);
}

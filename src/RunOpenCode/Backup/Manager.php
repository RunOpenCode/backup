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
namespace RunOpenCode\Backup;

use RunOpenCode\Backup\Contract\ManagerInterface;
use RunOpenCode\Backup\Contract\ProfileInterface;
use RunOpenCode\Backup\Contract\WorkflowInterface;

/**
 * Class Manager
 *
 * Backup manager.
 *
 * @package RunOpenCode\Backup
 */
final class Manager implements ManagerInterface
{
    /**
     * @var ProfileInterface[]
     */
    private $profiles;

    /**
     * @var WorkflowInterface
     */
    private $workflow;

    public function __construct(WorkflowInterface $workflow, $profiles = array())
    {
        $this->profiles = array();
        $this->workflow = $workflow;

        if (!empty($profiles)) {

            foreach ($profiles as $profile) {
                $this->add($profile);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(ProfileInterface $profile)
    {
        $this->profiles[$profile->getName()] = $profile;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return (isset($this->profiles[$name]));
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->profiles[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function execute($name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException(sprintf('Unknown profile: "%s".', $name));
        }

        $profile = $this->get($name);
        $this->workflow->execute($profile);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->profiles);
    }
}

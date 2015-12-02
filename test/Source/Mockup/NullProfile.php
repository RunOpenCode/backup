<?php

namespace RunOpenCode\Backup\Tests\Source\Mockup;

use RunOpenCode\Backup\Contract\ProfileInterface;

class NullProfile implements ProfileInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getNamer()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getPreRotator()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getPostRotator()
    {
        throw new \RuntimeException('This class is mockup of "RunOpenCode\\Backup\\Contract\\ProfileInterface" and none of its methods are implemented.');
    }
}
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
namespace RunOpenCode\Backup\Source;

use RunOpenCode\Backup\Contract\SourceInterface;

/**
 * Class SourceCollection
 *
 * Source collection enables fetching of backups from multiple sources.
 *
 * @package RunOpenCode\Backup\Source
 */
final class SourceCollection implements SourceInterface, \IteratorAggregate
{
    /**
     * @var SourceInterface[]
     */
    private $sources;

    /**
     * Constructor.
     *
     * @param SourceInterface[] $sources Initial sources to add.
     */
    public function __construct(array $sources = array())
    {
        foreach ($sources as $source) {
            $this->add($source);
        }
    }

    /**
     * Add source to collection.
     *
     * @param SourceInterface $source Source to add.
     * @return SourceInterface $this Fluent interface.
     */
    public function add(SourceInterface $source)
    {
        $this->sources = $source;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        $files = array();

        /**
         * @var SourceInterface $source
         */
        foreach ($this->sources as $source) {
            $files = array_merge($files, $source->fetch());
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->sources);
    }
}
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
namespace RunOpenCode\Backup\Processor;

use RunOpenCode\Backup\Contract\ProcessorInterface;

final class ProcessorCollection implements ProcessorInterface, \IteratorAggregate
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(array $processors = array())
    {
        $this->processors = array();

        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    /**
     * Add processor to collection
     *
     * @param ProcessorInterface $processor Processor to add to collection.
     * @return ProcessorCollection $this Fluent interface.
     */
    public function add(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $files)
    {
        foreach ($this->processors as $processor) {
            $files = $processor->process($files);
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->processors);
    }
}

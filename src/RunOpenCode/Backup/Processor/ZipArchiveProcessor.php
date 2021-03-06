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

use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Contract\ProcessorInterface;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\ProcessorException;
use RunOpenCode\Backup\Utils\Filename;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ZipArchiveProcessor
 *
 * Zip archive processor combines all backup file into single zip compressed archive.
 *
 * @package RunOpenCode\Backup\Processor
 */
class ZipArchiveProcessor implements ProcessorInterface, EventDispatcherAwareInterface
{
    use EventDispatcherAwareTrait;

    /**
     * @var string
     */
    protected $filename;

    public function __construct($filename = 'archive.zip')
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $files)
    {
        $tmpFile = Filename::temporaryFilename($this->filename);

        $processBuilder = new ProcessBuilder();

        $processBuilder
            ->add('zip')
            ->add($tmpFile);

        /**
         * @var FileInterface $backup
         */
        foreach ($files as $backup) {
            $processBuilder->add($backup->getPath());
        }

        $process = $processBuilder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessorException(sprintf('Unable to create zip archive, reason: "%s".', $process->getErrorOutput()));
        }

        $this->getEventDispatcher()->addListener(BackupEvents::TERMINATE, function() use ($tmpFile) {
            unlink($tmpFile);
        });

        return array(File::fromLocal($tmpFile, dirname($tmpFile)));
    }
}

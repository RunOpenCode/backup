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

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\FileInterface;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Contract\ProcessorInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\SourceException;
use RunOpenCode\Backup\Log\LoggerAwareTrait;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ZipArchiveProcessor
 *
 * Zip archive processor combines all backup file into single zip compressed archive.
 *
 * @package RunOpenCode\Backup\Processor
 */
class ZipArchiveProcessor implements ProcessorInterface, EventDispatcherAwareInterface, LoggerAwareInterface
{
    use EventDispatcherAwareTrait;
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function process(array $backups)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip-archive-processor');

        $processBuilder = new ProcessBuilder();

        $processBuilder
            ->add('zip')
            ->add($tmpFile);

        /**
         * @var FileInterface $backup
         */
        foreach ($backups as $backup) {
            $processBuilder->add($backup->getPath());
        }

        $process = $processBuilder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $this->getLogger()->error('Unable to create zip archive.');
            throw new SourceException();
        }

        $this->getEventDispatcher()->addListener(BackupEvent::TERMINATE, function() use ($tmpFile) {
            @unlink($tmpFile);
        });

        return File::fromLocal($tmpFile, dirname($tmpFile), 'archive.zip');
    }
}
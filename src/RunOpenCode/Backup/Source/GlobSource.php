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

use Psr\Log\LoggerInterface;
use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Contract\SourceInterface;
use RunOpenCode\Backup\Exception\SourceException;
use RunOpenCode\Backup\Log\LoggerAwareTrait;

/**
 * Class GlobSource
 *
 * GlobSource source uses glob expressions to determine which files should be backed up.
 *
 * @package RunOpenCode\Backup\Source
 */
class GlobSource implements SourceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $globs;

    public function __construct($glob)
    {
        if (is_array($glob)) {
            $this->globs = $glob;
        } else {
            $this->globs = array($glob => str_replace('*', '', $glob));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        $backupFiles = array();

        foreach ($this->globs as $glob => $rootPath) {
            $files = @glob($glob, GLOB_ERR);

            if ($files === false) {

                $this->getLogger()->error(sprintf('GlobSource expression "%s" is not correct and it fails getting list of files.', $glob), array(
                    'glob' => $glob
                ));

                throw new SourceException();

            } elseif (count($files)) {

                foreach ($files as $file) {
                    $backupFiles[] = File::fromLocal($file, $rootPath);
                }
            }
        }

        return $backupFiles;
    }
}
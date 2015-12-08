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

use RunOpenCode\Backup\Backup\File;
use RunOpenCode\Backup\Contract\SourceInterface;
use RunOpenCode\Backup\Exception\SourceException;

/**
 * Class GlobSource
 *
 * GlobSource source uses glob expressions to determine which files should be backed up.
 *
 * @package RunOpenCode\Backup\Source
 */
class GlobSource implements SourceInterface
{
    /**
     * @var string[]
     */
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
            $files = glob($glob, GLOB_ERR);

            if ($files === false) {

                throw new SourceException(sprintf('GlobSource expression "%s" is not correct and it fails getting list of files.', $glob));

            } elseif (count($files)) {

                foreach ($files as $file) {
                    $backupFiles[] = File::fromLocal($file, $rootPath);
                }
            }
        }

        return $backupFiles;
    }
}

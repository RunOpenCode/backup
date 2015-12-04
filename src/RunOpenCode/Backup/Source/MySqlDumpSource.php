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
use RunOpenCode\Backup\Contract\EventDispatcherAwareInterface;
use RunOpenCode\Backup\Contract\SourceInterface;
use RunOpenCode\Backup\Event\BackupEvents;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\SourceException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class MySqlDumpSource
 *
 * Fetch database dump for backup.
 *
 * @package RunOpenCode\Backup\Source
 */
class MySqlDumpSource implements SourceInterface, EventDispatcherAwareInterface
{
    use EventDispatcherAwareTrait;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    public function __construct($database, $user, $password = null, $host = null, $port = 3306)
    {
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        $processBuilder = new ProcessBuilder();

        $processBuilder
            ->add('mysqldump')
            ->add(sprintf('-u%s', $this->user));

        if (!is_null($this->host)) {
            $processBuilder->add(sprintf('-h%s:%s', $this->host, $this->port));
        }

        if (null !== $this->password) {
            $processBuilder->add(sprintf('-p%s', $this->password));
        }

        $processBuilder->add($this->database);

        $process = $processBuilder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new SourceException(sprintf('Unable to dump MySql database "%s", reason: "%s".', $this->database, $process->getErrorOutput()));
        }

        $tmpFile = tempnam(sys_get_temp_dir(), preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf('mysql-dump-%s-%s', $this->database, $this->host)));

        if (@file_put_contents($tmpFile, $process->getOutput()) === false) {

            throw new \RuntimeException(sprintf('Unable to save MySql dump of database into "%s".', $tmpFile));

        } else {

            $this->getEventDispatcher()->addListener(BackupEvents::TERMINATE, function() use ($tmpFile) {
                unlink($tmpFile);
            });

            return array(File::fromLocal($tmpFile, dirname($tmpFile), sprintf('mysql-dump-%s-%s-%s.sql', $this->database, $this->host, date('Y-m-d-H-i-s'))));
        }
    }
}
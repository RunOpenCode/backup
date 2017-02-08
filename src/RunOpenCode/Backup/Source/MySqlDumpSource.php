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
use RunOpenCode\Backup\Utils\Filename;
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

    public function __construct($database, $user, $password = null, $host = null, $port = null)
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
        $process = $this->buildProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new SourceException(sprintf('Unable to dump MySql database "%s", reason: "%s".', $this->database, $process->getErrorOutput()));
        }

        $mySqlDump = Filename::temporaryFile(sprintf('mysql-dump-%s-%s-%s.sql', $this->database, (is_null($this->host) ? 'localhost' : $this->host), date('Y-m-d-H-i-s')));

        if (file_put_contents($mySqlDump, $process->getOutput()) === false) {

            throw new \RuntimeException(sprintf('Unable to save MySql dump of database into "%s".', $mySqlDump));

        } else {

            $this->getEventDispatcher()->addListener(BackupEvents::TERMINATE, function() use ($mySqlDump) {
                unlink($mySqlDump);
            });

            return array(File::fromLocal($mySqlDump, dirname($mySqlDump)));
        }
    }

    /**
     * Builds mysqldump process.
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function buildProcess()
    {
        $processBuilder = new ProcessBuilder();

        $processBuilder
            ->add('mysqldump')
            ->add(sprintf('-u%s', $this->user));

        if (null !== $this->host) {

            if (null !== $this->port) {
                $processBuilder->add(sprintf('-h%s:%s', $this->host, $this->port));
            } elseif (file_exists($this->host)) {
                $processBuilder->add(sprintf('--protocol=socket -S %s', $this->host));
            } else {
                $processBuilder->add(sprintf('-h%s', $this->host));
            }
        }

        if (null !== $this->password) {
            $processBuilder->add(sprintf('-p%s', $this->password));
        }

        $processBuilder->add($this->database);

        return $processBuilder->getProcess();
    }
}

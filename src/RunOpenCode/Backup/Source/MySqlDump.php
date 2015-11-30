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
use RunOpenCode\Backup\Contract\LoggerAwareInterface;
use RunOpenCode\Backup\Contract\SourceInterface;
use RunOpenCode\Backup\Event\BackupEvent;
use RunOpenCode\Backup\Event\EventDispatcherAwareTrait;
use RunOpenCode\Backup\Exception\SourceException;
use RunOpenCode\Backup\Log\LoggerAwareTrait;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class MySqlDump
 *
 * Fetch database dump for backup.
 *
 * @package RunOpenCode\Backup\Source
 */
class MySqlDump implements SourceInterface, LoggerAwareInterface, EventDispatcherAwareInterface
{
    use LoggerAwareTrait;
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

    public function __construct($database, $user, $password = null, $host = 'localhost')
    {
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        $processBuilder = new ProcessBuilder();

        $processBuilder
            ->add('mysqldump')
            ->add(sprintf('-u%s', $this->user))
            ->add(sprintf('-h%s', $this->host));

        if (null !== $this->password) {
            $processBuilder->add(sprintf('-p%s', $this->password));
        }

        $processBuilder->add($this->database);

        $process = $processBuilder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $this->getLogger()->error(sprintf('Unable to dump MySql database "%s".', $this->database));
            throw new SourceException();
        }

        $tmpFile = tempnam(sys_get_temp_dir(), preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf('mysql-dump-%s-%s', $this->database, $this->host)));

        if (@file_put_contents($tmpFile, $process->getOutput()) === false) {

            $this->getLogger()->error(sprintf('Unable to save MySql dump of database into "%s".', $tmpFile));
            throw new \RuntimeException();

        } else {

            $this->getEventDispatcher()->addListener(BackupEvent::TERMINATE, function() use ($tmpFile) {
                @unlink($tmpFile);
            });

            return File::fromLocal($tmpFile, dirname($tmpFile), sprintf('mysql-dump-%s-%s-%s.sql', $this->database, $this->host, date('Y-m-d-H-i-s')));
        }
    }
}
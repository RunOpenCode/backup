Simple web application backup library
======

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/backup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/backup/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/backup/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/backup/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/backup/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/backup/build-status/master)
[![Build Status](https://travis-ci.org/RunOpenCode/backup.svg?branch=master)](https://travis-ci.org/RunOpenCode/backup)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/630d6a08-9ff8-4099-adda-8ff33e29a329/big.png)](https://insight.sensiolabs.com/projects/630d6a08-9ff8-4099-adda-8ff33e29a329)

**NOTE:** *This library is fork of 'kbond/php-backup' library with significant modifications which disables compatibility
with its original.*

Every process of backup can be broken down to several activities:

- Prepare files to backup from various sources (filesystem, database...)
- Process those files somehow (create zip archive, per example) and name it according to some convention
- Determine if there is sufficient backup space on your backup storage system, and delete old backups if neccessary
- Copy new backup to backup destination

Having in mind the process stated above, we can extrapolate several major parts of backup system:

- **Source** of files which needs to be backed up. Even database belongs to this category, since database backup is a file.
- **Backup** is collection of **Files** retrieved from Sources.
- **Processor** process Files within backup somehow (usually compress them into single archive, but it is not limited to mentioned).
- **Namer** provides new name for Backup according to some desired rule (timestamping or similar).
- **Rotator** checks previous backups within backup storage and removes old backups if some rotation constraint is violated 
              (per example, max size of backup storage).
- **Destination** is abstraction of backup storage where backups residue.
- **Workflow** is abstraction of backup process which is executed trough sequence of backup activities.
- **Profile** is collection of all above stated, it defines what have to backed up, how backup has to be processed, what 
              name to use, where to store new backups and how to rotate old backups.


# Source and File
Source is defined within `RunOpenCode\Backup\Contract\SourceInterface` and have only one method defined: `fetch`. Expected 
result from Source implementation is collection of `RunOpenCode\Backup\Contract\FileInterface`. File interface is abstraction
of file which is subject of backup process. Concrete implementation is provided within this library as 
`RunOpenCode\Backup\Backup\File`.

Backup library currently provides you with several `SourceInterface` implementations:

- `RunOpenCode\Backup\Source\NullSource` which is empty implementation of above mentioned interface which is used for testing
                                         purposes, but it can be used as well in production environment in conjunction with 
                                         events (which will be explained later on).
- `RunOpenCode\Backup\Source\GlobSource` which fetches files to backup from local drive using glob expressions (see [glob](http://php.net/manual/de/function.glob.php)
                                         for more details.)
- `RunOpenCode\Backup\Source\MySqlDumpSource` which fetches MySQL dump output in file and allows you to backup your MySQL 
                                              database.
- `RunOpenCode\Backup\Source\SourceCollection` which is collection of several `SourceInterface` implementations. It allows
                                               you to use several sources at once for your backup profile (per example, to backup
                                               both files and databases of your web application).

                                               
# Backup
Backup is abstraction of backup job, it is a collection of backup Files, and has its unique name.
 
# Processor
Usually, when we are doing some backup, we process our backup files (per example - we compress them into one single archive).
Processor is defined within `RunOpenCode\Backup\Contract\ProcessorInterface` and have only one method defined: `process`.


-------------------


This library is licensed under MIT license, same license as original library. For original library license, please visit:
[https://github.com/kbond/php-backup/blob/master/LICENSE](https://github.com/kbond/php-backup/blob/master/LICENSE).

For license of this library, see LICENSE file distributed with this package.

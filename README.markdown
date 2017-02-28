[DEPRECIATED] Simple web application backup library
======

**This library is depreciated, please use [https://phpbu.de](https://phpbu.de)**

------------------------------------

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/backup.svg)](https://packagist.org/packages/runopencode/backup)
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
- Copy new backup to backup destination (usually into new folder named with current date and time)

Having in mind the process stated above, we can identify several major parts of backup system:

- **Source** of files which needs to be backed up. Even database belongs to this category, since database backup is a file.
- **Backup** is collection of **Files** retrieved from Sources.
- **Processor** process Files within backup somehow (usually compress them into single archive, but it is not limited to mentioned).
- **Namer** provides new name for Backup according to some desired rule (timestamping or similar).
- **Rotator** checks previous backups within backup storage and removes old backups if some rotation constraint is violated 
              (per example, max size of backup storage).
- **Destination** is abstraction of backup storage where backups residue.
- **Workflow** is abstraction of backup process which is executed trough sequence of backup activities. This library provides 
               default workflow, which can be replaced with your own if default one does not suits your needs.
- **Profile** is collection of all above stated, it defines what have to backed up, how backup has to be processed, what 
              name to use, where to store new backups and how to rotate old backups.
- **Manager** is collection of profiles, he provides profiles with Logger and EventDispatcher and executes them.

*Note that author of this brilliant idea on how to brake down to fundamental backup system components is [Kevin Bond](https://github.com/kbond).*   


# See if this library is for you by evaluating simple example

Let's backup our website by creating simple application that will be executed by `crontab`. Let's say that our code is 
in some `app.php` file (Don't be scared with amount of code - each line is commented so you can understand the concept with
ease).

    <?php 
    /**
     * file: app.php
     */
    
    require_once('vendor/autoload.php'); // We are going to use composer for autoloading.
    
    use Psr\Log\NullLogger;
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use RunOpenCode\Backup\Source\MySqlDumpSource;
    use RunOpenCode\Backup\Source\SourceCollection;
    use RunOpenCode\Backup\Processor\ZipArchiveProcessor;
    use RunOpenCode\Backup\Namer\Timestamp;
    use RunOpenCode\Backup\Rotator\NullRotator;
    use RunOpenCode\Backup\Rotator\MaxCountRotator;
    use RunOpenCode\Backup\Destination\FlysystemDestination;
    use RunOpenCode\Backup\Backup\Profile;
    use RunOpenCode\Backup\Workflow\Workflow;
    use RunOpenCode\Backup\Manager;
    
    use League\Flysystem\Dropbox\DropboxAdapter;
    use League\Flysystem\Filesystem;
    use Dropbox\Client;
    
    $logger = new NullLogger();                                 // Or you can use concrete logger if you like.
    $eventDispatcher = new EventDispatcher();                   // We need event dispatcher as well.

    // Let's define source of our website files, we will use glob source to fetch files
    // Array keys are path to directories where files residue, while values are path prefixes which we would like to remove
    // and work only with relative paths
    $files = new GlobSource(array(
        '/path/to/directory/with/files' => 'path/to/directory',           
        '/other/path/to/directory/with/files' => 'other/path'            
    ));

    // Let's backup database as well...
    $settings = array(...);
    $database = new MySqlDumpSource($settings['database'], $settings['username'], $settings['password'], $settings['host'], $settings['port']);

    // Our files and databases are things that we are want to backup, so we have to use source collection...
    $source = new SourceCollection(array(
        $files,
        $database
    ));
    
    // We will zip our backup files in order to save storage space 
    $processor = new ZipArchiveProcessor('archive.zip');
    // Note that processor requires event dispatcher...
    $processor->setEventDispatcher($eventDispatcher);

    // Our backups will get name based on current timestamp
    $namer = new Timestamp();
    
    // We will not use pre-rotator...
    $preRotator = new NullRotator();
    // But we will use post-rotator... (see docs below for difference, this is just example) limiting number of backups to some number.
    $postRotator = new MaxCountRotator(5);
    
    // And let's define our backup storage, per example, Dropbox via Flysystem (which is optional).
    // @see http://flysystem.thephpleague.com/adapter/dropbox/
    $client = new Client($accessToken, $appSecret);
    $adapter = new DropboxAdapter($client, [$prefix]);
    $filesystem = new Filesystem($adapter);
    $destination = new FlysystemDestination($filesystem);
    
    // When we have components, lets define our profile:
    $profile = new Profile('my-profile', $source, $processor, $namer, $preRotator, $destination, $postRotator);
    
    // We need workflow for manager, so we will build it and provide it with logger and event dispatcher
    $workflow = Workflow::build();
    $workflow->setEventDispatcher($eventDispatcher);
    $workflow->setLogger($logger);
    
    // Finally, we will create manager, and feed him with profile.
    $manager = new Manager($workflow, array($profile));
    
    // And we can now execute backup process...
    $manager->execute('my-profile');
    
This library provides you with very flexible way to define and execute your backups. However, library is intended to be
used within frameworks where profile construction process ought to be simplified via various configurations possibilities 
of concrete framework.
    
Do read further more to find out how to use and extend library to your needs.    

# Source and File

Source is defined within `RunOpenCode\Backup\Contract\SourceInterface` and have only one method defined: `fetch()`. Expected 
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
Processor is defined within `RunOpenCode\Backup\Contract\ProcessorInterface` and have only one method defined: `process(array $files)`.

Purpose of processor is to somehow modify the collection of files scheduled for backup, and to return the resulting files
of that modification.

Backup library currently provides you with several `ProcessorInterface` implementations:

- `RunOpenCode\Backup\Processor\NullProcessor` which is empty implementation of above mentioned interface. It is used for testing
                                               purposes, however, it can be used when you do not want to process backup files (per
                                               example, for incremental snapshot backups).
- `RunOpenCode\Backup\Processor\GzipArchiveProcessor` which compress all backup files into one gzip archive. 
                                                      Requires gzip installed on system, accessible via console.
- `RunOpenCode\Backup\Processor\ZipArchiveProcessor` which compress all backup files into one zip archive.
                                                     Requires zip installed on system, accessible via console.
- `RunOpenCode\Backup\Processor\ProcessorCollection` which is collection of several implementations of `ProcessorInterface`
                                                     that allows you to do several successive processing activities agains
                                                     backup files.

# Namer
                                                     
Your backup files will be stored in one directory in your backup storage. Namer will provide a name for that directory.
Namers implement `RunOpenCode\Backup\Contract\NamerInterface` which contains only one method: `getName()`. 

Backup library provides you with two default namer implementations:

- `RunOpenCode\Backup\Namer\Constant` which will always provide exact same name for your new backup directory.
- `RunOpenCode\Backup\Namer\Timestamp` which will provide you with name of directory based on current date and time.

Depending on naming strategy, you can get different results with your backup. With `RunOpenCode\Backup\Namer\Constant`
you can implement incremental backup, snapshot backup, which will behave as `rsync` Unix utility. With timestamp namer,
`RunOpenCode\Backup\Namer\Timestamp`, each new backup can be stored in new directory. 

However, you can configure timestamp namer to use, per example, only day of the week, so you can store only last 7 
backups without using rotators. 

Nevertheless, it is advise to use namers for defining weather it will be a incremental backup, or complete backup, while
rotators should be used for rotations of old backups.

# Rotator

Rotator will nominate old backups for removal from backup storage, if some conditions are met. Rotator is defined with 
`RunOpenCode\Backup\Contract\RotatorInterface` and have only one method: `nominate(array $backups)`. Rotator can not 
remove old backups, rotator is not aware of backup storage, his only role in process is to nominate old backups for 
removal from list of backups.

Backup library provides you with several rotators:

- `RunOpenCode\Backup\Rotator\NullRotator` which never nominates any backup for removal. It is used for testing purposes,
                                           however, it can be used in your backup process if you want to keep all backups.
- `RunOpenCode\Backup\Rotator\MaxCountRotator` will keep up to maximum defined number of backups. If there are more backups
                                               than maximum allowed, oldest backups get nominated for removal.
- `RunOpenCode\Backup\Rotator\MaxSizeRotator` will keep up to maximum total size of all backups. If size of backups is
                                              larger than maximum allowed, oldest backups get nominated for removal.
- `RunOpenCode\Backup\Rotator\MinCountMaxSizeRotator` nominates backups for removal using same method as `MaxSizeRotator`,
                                                      however, rotator will keep minimum defined number of backups even
                                                      if maximum size constrain is violated.
- `RunOpenCode\Backup\Rotator\RotatorCollection` can be used to aggregate nominations from several different rotators.


Note that default backup workflow within this library defines pre-rotation and post-rotation. Pre-rotation is executed
before backup is copied to backup storage (destination), while post-rotation is executed after backup is copied to backup
storage.

The reason behind this is that file system operations are not transactional. If removal of old backup is executed prior 
to pushing new backup to backup storage, and coping fails, you could end up with one backup less as a result of operation.
However, if removal of old backup is executed after pushing new backup to backup storage, and coping fails, you would still
keep old backups.
                                                                     
Pre and post rotation should support wishes of booth more and/or less conservative system administrator.                                                                      
                                                     
# Destination

Destination is abstraction of backup storage. Each destination implements `RunOpenCode\Backup\Contract\Destination` interface
which is most complex component in this library and have several responsibilities:

- Copies backup files from source to backup destination into relevant directory. If that directory is not empty, it will
  synchronize source files with files in that directory providing the user with possibility to have incremental backups
  (method: `push(BackupInterface $backup)`). Directory name should be same as sanitized backup name.
- Provides getters and hasers for backups and enables iteration trough existing backups (methods: `get($name)`, 
  `has($name)`, `all()` and implementing interfaces `\IteratorAggregate` and `\Countable`).
- Supports removal of existing backups (method: `delete($name)`). 

Backup library provide you with several default implementations:

- `RunOpenCode\Backup\Destination\NullDestination` which is used for testing purposes, but it can be used in production
                                                   environments in conjunction with events (which will be explained latter
                                                   on).
- `RunOpenCode\Backup\Destination\LocalDestination` which is abstraction of local file system. You can use local destination
                                                    when backup storage is on mountable device. `LocalDestination` requires
                                                    `symfony/filesystem` to be installed.
- `RunOpenCode\Backup\Destination\FlysystemDestination` can be used in conjunction with optional package `league/flysystem`.
                                                        You can read more about Flysystem [here](http://flysystem.thephpleague.com).
                                                        In general, Flysystem is abstraction of various storage systems, which includes,
                                                        but not limited to, Dropbox, Azure, AWS, etc.

Additionally, you are provided with possibility to use multiple destinations for your backups, by using:
                                                        
- `RunOpenCode\Backup\Destination\DestinationCollection` which is collection of several destinations. This will allow you
                                                         to have redundant copies of your backups. Note that `DestinationCollection`
                                                         `push()` method will fail if any of destination within the collection fails with 
                                                         `push()` method.
- `RunOpenCode\Backup\Destination\ReplicatedDestination` defines master destination and slave destination. Difference between 
                                                         `ReplicatedDestination` and `DestinationCollection` is that if slave destination
                                                         in `ReplicatedDestination::push()` method fails, backup will not fail, 
                                                         it will be considered as successful. 
                                                         
Note that you can combine `ReplicatedDestination` and `DestinationCollection` to achieve various different backup 
storage systems, from simple ones to very complex.                                                                                                                   
                                                        
# Workflow
                                                  
Workflow is abstraction of backup process, a sequence of activities which needs to be undertaken for backup process to be 
successfully completed.
                                                  
Workflow is defined with `RunOpenCode\Backup\Contract\WorkflowInterface`, while workflow activity is defined with 
`RunOpenCode\Backup\Contract\WorkflowActivityInterface`. In that matter, you can consider a Workflow as collection of 
Activities, executed in ordered sequence.

Backup library provides you with default implementation of workflow: `RunOpenCode\Backup\Workflow\Workflow` with static 
method `build()` that will create default workflow with following activities in sequence:

1. `RunOpenCode\Backup\Workflow\Fetch` activity in which files for backup are fetched from source.
2. `RunOpenCode\Backup\Workflow\Process` activity in which files for backup are processed.
3. `RunOpenCode\Backup\Workflow\Name` activity in which backup gets its name.
4. `RunOpenCode\Backup\Workflow\PreRotate` activity in which existing old backups on destination are rotated. 
5. `RunOpenCode\Backup\Workflow\Push` activity in which backup is pushed to destination.
6. `RunOpenCode\Backup\Workflow\PostRotate` activity in which existing old backups on destination are rotated.

Note that you can modify this workflow to suit your needs, if provided one is not according to your desired backup
workflow. However, this can be considered as edge case.

You should note that default implementation of workflow, `RunOpenCode\Backup\Workflow\Workflow` depends on 
`Symfony\Component\EventDispatcher\EventDispatcherInterface` and `Psr\Log\LoggerInterface`, as well as
provided workflow activities. However, neither workflow, nor its activities, resolves that dependency during the 
construction process. Workflow will provide EventDispatcher and Logger to the activities prior to their execution via 
setters, while workflow should be provided with mentioned prior to its execution.

## Events

Events and Symfony EventDispatcher are major difference between this library and original `kbond/php-backup` library.
Events which are dispatched within this library and default workflow are defined in `RunOpenCode\Backup\Event\BackupEvents`
while dispatched events are instance of `RunOpenCode\Backup\Event\BackupEvent`.

Events are used to follow up every defined backup workflow activity which allows you to:

- **Modify and/or filter results of each workflow activity.** Per example, you can use `RunOpenCode\Backup\Source\NullSource`
  which will return no files for backup, and add files for backup manually by hooking up to `BackupEvents::FETCH` event. 
  In that matter, every "Null" implementation in this library makes sense and can be used in production in conjunction with
  EventDispatcher.
- **Release and clean up resources which are not required anymore.** Some implementations of this library components requires
  some kind of cleaning up. Per example, backup of MySQL database requires usage of temporary file, which ought to be cleaned
  up when backup process is terminated. By hooking up to `BackupEvents::TERMINATE` event, `RunOpenCode\Backup\Source\MySqlDumpSource`
  gets notified when temporary file is not used anymore and can be removed from system.  

By using event dispatching, API for backup components is simplified - there is no need for `cleanUp()` methods.

**Important note:** some events will be dispatched, and some won't. However, in your application, you can always count
on following events:

- `BackupEvents::BEGIN` will be dispatched when backup is started for some profile.
- `BackupEvents::TERMINATE` will be dispatched when backup is terminated for some profile, regardless of its result of 
  execution. Use this event as indicator when to clean up all used temporary files and to release all resources.

Other events depends on workflow and result of each workflow activity, as well as fact if there were some error in execution. 

# Profile

Profile is defined with `RunOpenCode\Backup\Contract\ProfileInterface` while default implementation 
`RunOpenCode\Backup\Backup\Profile` is provided. Backup profile defines:
 
- Source of files for backup.
- Processor which will proces those files.
- Namer which will provide the name for each new backup.
- Pre and Post rotators which will rotate existing old backups.
- Destination where backup will be stored.

If you think about your project, application which you want to backup, profiles for your application would be, per example:

- Hourly snapshot - profile would be executed every hour.
- Daily backup - profile would be executed every day.
- Weekly backup - profile would be executed every week.
- ...
                                                 
# Manager
                                                 
Manager is defined with `RunOpenCode\Backup\Contract\ManagerInterface`, default implementation is given with `RunOpenCode\Backup\Manager`.
He holds references to all profiles, allows iteration trough profiles, and their execution. If you are using dependency 
injection or service locator in your project, Manager should be only one public entry point into library, while other 
components should be injected into manager as hidden/private dependencies. 


# Notes on EventDispatcher, Logger and throwing exceptions

Note that some of the components in library depends on event dispatcher and/or logger. However, dependency is not provided 
via constructor, it is provided via setters. Some of the components do depend on dispatcher and/or logger, some don't.

In order to identify weather some class depends on dispatcher you can investigate if that class implements 
`RunOpenCode\Backup\Contract\EventDispatcherAwareInterface`, while logger dependency can be investigated by checking
if `RunOpenCode\Backup\Contract\LoggerAwareInterface` is implemented.

Instances of `RunOpenCode\Backup\Contract\WorkflowInterface` and `RunOpenCode\Backup\Contract\WorkflowActivityInterface`
depends on logger and dispatcher by design. They will log about progress of backup process and dispatch backup progress
events.
 
Some other classes within this library depends on event dispatcher in order to clean up temporary files and to release
resources. 
 
However, do note that by design it is intended for workflow and its activities to log and to dispatch events. Other components
should subscribe to events only. If they have to notify about error - they should throw exception.

## Logger and Event Dispatcher traits

To simplify your implementation, when implementing `RunOpenCode\Backup\Contract\EventDispatcherAwareInterface` and
`RunOpenCode\Backup\Contract\LoggerAwareInterface`, please note that

- `RunOpenCode\Backup\Event\EventDispatcherAwareTrait`
- `RunOpenCode\Backup\Log\LoggerAwareTrait`

are at your disposal.

# Extending the library
                                               
Do you need your own source, destination, processor? You can easily extend the library.
                                               
## Implementing your own source

Your class needs to implement `RunOpenCode\Backup\Contract\SourceInterface`. Method `fetch()` should return collection
of files `RunOpenCode\Backup\Contract\File` for backup. 
                                               
Note that each file has its path and relative path. Path is absolute path to file, so backup library can access to it and
copy it to the backup destination. However, within backup directory, file will be saved under relative path. Relative path 
is determined by root path of file.

## Implementing your own processor

Your class needs to implement `RunOpenCode\Backup\Contract\ProcessorInterface` with method `process(array $files)`. You will 
get an collection of files that needs to be backed up. Your processor should do something with those files, and return collection
of files that should be backed up after processing.

## Implementing your own rotator

Your class needs to implement `RunOpenCode\Backup\Contract\RotatorInterface`. Rotator will get collection of backups on backup
destination and should only nominate which backups should be removed. 
                                                     
## Implementing your own destination

Your class needs to implement `RunOpenCode\Backup\Contract\Destination`. Destination is collection of 
`RunOpenCode\Backup\Contract\BackupInterface`, while it needs to be noted that physically, for each backup, 
destination will create a directory and store all backup files within that directory.

When implementing method `push(BackupInterface $backup)` destination should support creating new backups, as well as
maintaining incremental backups. That means that if backup directory exists on destination, on push, destination should
sync source files with files that exists in backup directory. To speed up implementation, 
`RunOpenCode\Backup\Destination\BaseDestination` is at your disposal.

  


-------------------


This library is licensed under MIT license, same license as original library. For original library license, please visit:
[https://github.com/kbond/php-backup/blob/master/LICENSE](https://github.com/kbond/php-backup/blob/master/LICENSE).

For license of this library, see LICENSE file distributed with this package.

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

- Source
- Processor
- Namer
- Rotator
- Destination


-------------------


This library is licensed under MIT license, same license as original library. For original library license, please visit:
[https://github.com/kbond/php-backup/blob/master/LICENSE](https://github.com/kbond/php-backup/blob/master/LICENSE).

For license of this library, see LICENSE file distributed with this package.

cake-databaseLog
================

Simple storing CakePHP 2.x log into a database.

## Installation

 Add `"freefri/cake-databaselog": "dev-master",` to your *composer.json* or clone the repository into `Plugin/CakeDatabaselog`

 Load the plugin in your bootstrap.php `CakePlugin::load('CakeDatabaselog');`

 Create a table in your database kind of this:

     CREATE TABLE `log_entries` (
       `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
       `type` varchar(50) DEFAULT NULL,
       `title` varchar(30) DEFAULT NULL,
       `message` text,
       `environment` varchar(100) DEFAULT NULL,
       `server` text,
       `created` datetime DEFAULT NULL,
       PRIMARY KEY (`id`)
     ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

 Use the new engine to log in bootstrap.php

     CakeLog::config(
         'debug', [
             'engine' => 'CakeDatabaselog.DatabaseLog',
             'types' => ['notice', 'info', 'debug'],
             'environment' => 'production-server',
         ]
     );

## Related projects
You may also be interested in a more complex solution: https://github.com/dereuromark/CakePHP-DatabaseLog (those two projects are not related)

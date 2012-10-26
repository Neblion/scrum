Neblion/Scrum Symfony scrum app
===============================

Neblion/Scrum is a Symfony 2 scrum app.

[![Build Status](https://secure.travis-ci.org/Neblion/scrum.png)](http://travis-ci.org/Neblion/scrum)


Installation
------------
### Requirements
You have to install and configure a Web server (such as Apache) with a recent 
version of PHP5 (5.3.8 or newer is recommended) and a MySQL server 5.0 or newer.
You also have to install [composer](http://getcomposer.org/) (dependency management library for PHP), 
you should install it [globally](http://getcomposer.org/doc/00-intro.md#globally) on your system.

As Neblion/Scrum is a symfony app, you can find more informations on Symfony2 
requirements at [requirements reference](http://symfony.com/doc/current/reference/requirements.html "Symfony2 requirements reference").
For information on configuring your specific web server document root, 
see the following documentation: [Apache](http://httpd.apache.org/docs/current/mod/core.html#documentroot).

### Step by step installation
1.  Install via composer and packagist

        composer create-project neblion/scrum <your-installation-path>

2.   Create you vhost and configure DocumentRoot to --> `<your-path>/web` and check your config: http://`<your-host>`/config.php

3.   Set permission on file system see [Symfony2 install](http://symfony.com/doc/current/book/installation.html#configuration-and-setup).

4.   Create your DB and a user DB:

        mysql -uroot -p
        <enter_mysql_root_pass>
        create database <DB_NAME>;
        grant all privileges on <DB-NAME>.* to '<YOUR-USERNAME>'@'localhost' identified by 'YOUR-PASSWORD' with grant option;
        flush privileges;

5.   Set your parameters.yml

    Copy the distribution file parameters.yml.dist to parameters.yml and edit it.

6.   Run commands

        cd <your-installation-path>
        php app/console doctrine:schema:update --force
        php app/console doctrine:load:fixtures --fixtures=./src/Neblion/ScrumBundle/DataFixtures
        php app/console assets:install
        php app/console assetic:dump

7.  All done, test it!

Documentation
-------------
Work in progress...

Support and contact
-------------------
scrum@neblion.net

Tests
-----
    phpunit -c app

License
-------
Neblion/Scrum is a free software licensed under the GNU Affero General Public License V3.


Credits
-------
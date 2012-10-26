Neblion/Scrum Symfony scrum app
===============================

Neblion/Scrum is a Symfony 2 scrum app.

[![Build Status](https://secure.travis-ci.org/Neblion/scrum.png)](http://travis-ci.org/Neblion/scrum)

Feature Overview
----------------


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
        composer create-project neblion/scrum <your-path>

*   Create you vhost and configure DocumentRoot to --> <your-path>/web
    Check your config: http://<your-host>/config.php

*   Set permission on file system see [Symfony2 install](http://symfony.com/doc/current/book/installation.html#configuration-and-setup).

*   Create your DB and a user DB.

*   Set your parmeters.yml

*   Run commands
        cd <your-installation-path>
        php app/console doctrine:schema:update --force
        php app/console doctrine:load:fixtures --fixtures=./src/Neblion/ScrumBundle/DataFixtures/Init
        php app/console assets:install
        php app/console assetic:dump


Documentation
-------------


License
-------
Neblion/Scrum is a free software licensed under the GNU Affero General Public License V3.


Credits
-------
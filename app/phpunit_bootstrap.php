<?php

require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';
 
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
 
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
 
$kernel = new AppKernel('test', true); // create a "test" kernel
$kernel->boot();
 
$application = new Application($kernel);
 
// add the database:drop command to the application and run it
$command = new DropDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:database:drop',
    '--force' => true,
));
$command->run($input, new ConsoleOutput());
 

// add the database:create command to the application and run it
$command = new CreateDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:database:create',
));
$command->run($input, new ConsoleOutput());

// Hack 
$connection = $application->getKernel()->getContainer()->get('doctrine')->getConnection();
if ($connection->isConnected()) {
    $connection->close();
}
// let Doctrine create the database schema (i.e. the tables)
$command = new CreateSchemaDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:schema:create',
));
$command->run($input, new ConsoleOutput());

//load fixtures
$command = new LoadDataFixturesDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:fixtures:load',
    '--fixtures'   => 'src/Neblion/ScrumBundle/DataFixtures/Tests'
));
$command->run($input, new ConsoleOutput());
 
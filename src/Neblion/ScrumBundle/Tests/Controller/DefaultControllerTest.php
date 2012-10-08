<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        //$this->assertTrue($crawler->filter('html:contains("Dashboard")')->count() == 0);
        //$this->assertTrue($crawler->filter('html:contains("Hello, world!")')->count() == 1);
        
        $form = $crawler->selectButton('Login')->form();
        $client->submit($form, array(
            '_username' => 'admin',
            '_password'  => 'test'
        ));
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        //var_dump($client->getResponse()->getContent());
    }
    
    public function testAuth()
    {
        /*
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        var_dump($client->getResponse()->getContent());
        $this->assertTrue($crawler->filter('html:contains("Your projects")')->count() == 2);
        */
    }
}

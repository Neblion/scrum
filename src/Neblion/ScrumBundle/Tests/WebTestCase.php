<?php

namespace Neblion\ScrumBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function login($username, $password)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        
        $form = $crawler->selectButton('Login')->form();
        $client->submit($form, array(
            '_username'     => $username,
            '_password'     => $password
        ));

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        return $client;
    }
}
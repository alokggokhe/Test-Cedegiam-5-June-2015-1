<?php

namespace MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class HomeControllerTest extends WebTestCase
{
	private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testLogin()
    {
      
	   $crawler = $this->client->request('POST', '/');
	   $form = $crawler->selectButton('Enter')->form(array(
	     'login'  => 'stephane.kesy@cegedim.fr',
	     'password'=> '123456',
	     'service' => 'http://192.168.8.237:8080/option',
	     '_flowId' => 'loginext-webflow',
	     'errorRedirectUrl' => 'http://192.168.8.237:8000/home?error=login'
	   ));

	   $crawler =  $this->client->submit($form);
	   //$crawler =  $this->client->followRedirect();
	   echo "<pre>";
	   print_r($crawler);
	   echo "<pre>";
	   die;
	   return false;
           $this->assertTrue($crawler->filter('h1:contains("Option Page")')->count() > 0);
    }
}

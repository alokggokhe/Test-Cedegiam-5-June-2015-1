<?php

namespace MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ForgottenPasswordControllerTest extends WebTestCase
{
	
	private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testindexAction()
    {

        $crawler = $this->client->request('POST', '/forgotten_password');

        $this->client->getContainer()->get('session');
       
        $form = $crawler->selectButton('Enter')->form();
       
        $crawler = $this->client->submit($form,
                    array('email'           => 'nirajm@alohatechnology.com')
                    );
        $this->assertGreaterThan(-1, $crawler->filter('html:contains("Log In")')->count());
    }

     /*
    * @depends testIndex
    **/
    public function testCancelAction()
    {
        $this->client->getContainer()->get('session');
        $firewall = 'secured_area';
        $crawler = $this->client->request('GET', '/forgotten_password');
        $link    = $crawler->filter('a:contains("CANCEL")')->eq(0)->link();
        $crawler = $this->client->click($link);
        
        //Check the h2 has the blog title in it
        $this->assertEquals(0, $crawler->filter('html:contains("Log In")')->count());
    }

    /*
	* @depends testIndex
	**/
	public function testPolicyRedirect()
    {
	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/forgotten_password');
		$link 	 = $crawler->filter('a:contains("Privacy Policy")')->eq(0)->link();
		$crawler = $this->client->click($link);
		
    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('html:contains("150112_Legal%20Policy_UCBDandelion.pdf")')->count());
	}
}
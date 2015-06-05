<?php
namespace AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvitationControllerTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testindexAction()
    {

        $crawler = $this->client->request('GET', '/invitation');
        $this->client->getContainer()->get('session');
      
        $form = $crawler->selectButton('Submit')->form();
        $form['invitation[topics]'][0]->tick();

        $crawler = $this->client->submit($form,
                    array(  'invitation[question]'              => 'TestCasesqQuestion',
                            'invitation[relatedToAE]'           => '1',
                            'invitation[availabilityDetails]'   => 'immediately',
                            'invitation[datetime]'              => '2015-05-28 10:30',
                            'invitation[name]'                  => 'TestCasesName',
                            'invitation[phone]'                 => '3333333333',
                            'invitation[topics]'                => array('0' => 5,'1' => 8),
                            'invitation[email]'                 => 'testcasesqquestion@gmail.com')
                    );
        $this->assertGreaterThan(0, $crawler->filter('html:contains("confirm")')->count());
    }

    /*
    * @depends testIndex
    **/
    public function testCancelAction()
    {
        $this->client->getContainer()->get('session');
        $firewall = 'secured_area';
        $crawler = $this->client->request('GET', '/invitation');
        $link    = $crawler->filter('a:contains("Cancel")')->eq(0)->link();
        $crawler = $this->client->click($link);
        
        //Check the h2 has the blog title in it
        $this->assertEquals(0, $crawler->filter('html:contains("Option page")')->count());
    }
}
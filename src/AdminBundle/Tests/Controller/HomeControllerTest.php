<?php

namespace AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
	private $client 			= null;
	public static $lastMlsId 	= 0;
	public static $lastAreaId 	= 0;	

	public function setUp()
	{
		$this->client = static::createClient();
	} 
	
	public function testLogin()
	{
		$crawler = $this->client->request('POST', '/admin/');
		$form = $crawler->selectButton('Connect')->form(array(
			'_username'  => 'ucb@ucb.com',
			'_password'=> 'aloha@123'
		));
		$crawler =  $this->client->submit($form);
		$session = $this->client->getContainer()->get('session');
		$response_code = $this->client->getResponse()->getStatusCode();
	}
		
	public function testUserAddIndex()
	{
		$this->testLogin();
		$crawler = $this->client->request('GET', '/admin/therapeutic_area_user');
		$crawler = $this->client->request('POST', '/admin/msl_add', array(
			'txt_first_name'  => 'TestCasesFirst',
			'txt_last_name' => 'TestCasesLast',
			'txt_email' => 'TestCasesLast17@mail.com')
		);
		$response 	= $this->client->getResponse()->getContent();
		$jsonResponse 	= json_decode($response);
		HomeControllerTest::$lastMlsId 	= $jsonResponse->data;
		$this->assertTrue($this->client->getResponse()->isSuccessful());
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
	}

	/*
	* @depends testUserAddIndex
	**/
	public function testUserGetAction()
	{	
		$this->testLogin();
		$lastInsertId = HomeControllerTest::$lastMlsId;
		$crawler = $this->client->request('POST', '/admin/msl_get',array('msl_id' => $lastInsertId));
	}

	/*
	* @depends testUserGetAction
	**/
	public function testUserEditAction()
	{		
		$this->testLogin();
		$crawler = $this->client->request('POST', '/admin/therapeutic_area_user');
		$form 	 = $this->client->request('POST', '/admin/msl_edit',array(
			'edit_msl[email]'	 => 'listTestcases@gmail.com',
			'edit_msl[firstName]'	 => 'listTestcasesFirst',
			'edit_msl[gender]'	 => 'Male',
			'edit_msl[lastName]'	 => 'listTestcaseslast',
			'edit_msl[mslTerritory]' => 'listTestcasesTerritory',
			'edit_msl[role]'	 => 'MSL',
			'hid_msl_id'		 => HomeControllerTest::$lastMlsId)
		);
	}

	/*
	* @depends testUserEditAction
	**/
	public function testUserDeleteAction()
	{		
		$this->testLogin();	
		$crawler = $this->client->request('POST', '/admin/msl_delete',array('msl_id' => HomeControllerTest::$lastMlsId ));
	}

	/*
	* @depends testUserDeleteAction
	**/
	public function testTherapeuticAreaAddAction()
	{		
		$this->testLogin();
		$crawler = $this->client->request('POST', '/admin/therapeutic_area_add', array(
			'txt_therapeutic_area'  => 'TestCaseArea9')
		);
		$response = $this->client->getResponse()->getContent();
		$jsonResponse 	= json_decode($response);
		HomeControllerTest::$lastAreaId = $jsonResponse->data;
	}	
	
	/*
	* @depends testTherapeuticAreaAddAction
	**/
	public function testTherapeuticAreaeditAction()
	{		
		$this->testLogin();
		$crawler = $this->client->request('POST', '/admin/therapeutic_area_edit',array(
			'hid_therapeutic_area_id'  => HomeControllerTest::$lastAreaId,
			'txt_edit_therapeutic_area'=> 'TestCasesArea'
		));
	}	
	
	/*
	* @depends testTherapeuticAreaeditAction
	**/
	public function testLogout()	 {
		$crawler = $this->client->request('POST', '/admin/logout');
	}
}

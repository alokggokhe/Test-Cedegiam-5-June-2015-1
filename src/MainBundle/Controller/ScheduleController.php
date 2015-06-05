<?php

namespace MainBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;

use MainBundle\Entity\Schedule;
use Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser;

class ScheduleController extends Controller
{
	/**
	* Add Shcedule
	*
	*/
	public function addAction(Request $request)
	{
		$local_timezone = $this->get('session')->get('local_timezone');
		$schedule = new Schedule();
		$form = $this->createForm('schedule', $schedule, array('view_timezone' => $local_timezone));
		$form->handleRequest($request);
		if ($form->isValid()) {
			$doctrine = $this->getDoctrine()->getManager();
			$doctrine->persist($schedule);
			$doctrine->flush();
			$this->sendCreateEditMail($schedule->getId(), 'Add');
			return $this->redirect($this->generateUrl('schedule_confirm', array('id' => $schedule->getId())));
		}
		$ucb_patient_action = $this->container->getParameter('ucb_patient_login');
		return $this->render('MainBundle:Schedule:edit.html.twig', array(
			'form' 					=> $form->createView(),
			'ucb_patient_action' 	=> $ucb_patient_action
		));
	}

	/**
	* Edit Shcedule
	*
	*/
	public function editAction(Request $request, $id)
	{
		$local_timezone = $this->get('session')->get('local_timezone');
		$doctrine = $this->getDoctrine()->getManager();
		$schedule = $doctrine->getRepository('MainBundle:Schedule')->find($id);
		if (!$schedule) {
			return $this->redirect($this->generateUrl('option'));
		}
		$form = $this->createForm('schedule', $schedule, array(
			'action' => $this->generateUrl('schedule_edit', array('id' =>  $id)),
			'view_timezone' => $local_timezone
		));
		$form->handleRequest($request);
		if ($form->isValid()){
			$data = $form->getData();
			return $this->render('MainBundle:Schedule:schedule_confirm_edit.html.twig', array(
				'request' => $data
			));
		}
		
		$ucb_patient_action = $this->container->getParameter('ucb_patient_login');
		return $this->render('MainBundle:Schedule:edit.html.twig', array(
			'schedule' 				=> $schedule,
			'form'   				=> $form->createView(),
			'ucb_patient_action' 	=> $ucb_patient_action
		));
	}

	/**
	* List Shcedule
	* $action - upcoming/get (Upcoming/Manage)
	*
	*/
	public function listAction($action)
	{
		$this->updateStatusFromWebService();
		$owauuid 		= '';
		$owaonekeycode 	= '';
		$local_timezone = $this->get('session')->get('local_timezone');
		if($this->get('security.context')->getToken()->getUser() instanceof OwaUser){
			$owauuid 		= $this->get('security.context')->getToken()->getUser()->getUuid();
			$owaonekeycode 	= $this->get('security.context')->getToken()->getUser()->getOnekeycode();
		}

		if($owauuid == '' && $owaonekeycode == '' || $action == '') {
			return $this->redirect($this->generateUrl('option'));
		}

		$doctrine = $this->getDoctrine()->getManager();

		if($action == 'upcoming'){
			$schedule = $doctrine->getRepository('MainBundle:Schedule')->getUpcomingSchdule($owaonekeycode,$local_timezone);	
		} if($action == 'get'){
			$schedule = $doctrine->getRepository('MainBundle:Schedule')->getFromTodaySchdule($owaonekeycode,$local_timezone);
		}

		$ucb_patient_action = $this->container->getParameter('ucb_patient_login');
		return $this->render('MainBundle:Schedule:list.html.twig', array(
			'schedules' 			=> $schedule,
			'ucb_patient_action' 	=> $ucb_patient_action,
			'action' 				=> $action
		));
	}

	/**
	* Schedule Confirm after adding/eiditing the schedule
	*
	*/
	public function scheduleConfirmAction(Request $request)
	{

		if($request->get('id') == '') {
			return $this->redirect($this->generateUrl('option'));
		}

		$doctrine  = $this->getDoctrine()->getManager();
		$schedule  = $doctrine->getRepository('MainBundle:Schedule')->find($request->get('id'));

		if($request->get('action') == 'Edit') {

			//change status to 'Confirmed (Edited)'
			$schedulestatus = $doctrine->getRepository('MainBundle:ScheduleStatus')->find(2);
			$schedule->setScheduleStatus($schedulestatus);
			$schedule->setTitle($request->get('title'));
			$schedule->setPhone($request->get('phone'));
			$schedule->setScheduledatetime(new \DateTime($request->get('scheduledatetime')));
			$doctrine->persist($schedule);
			$doctrine->flush();
			$this->sendCreateEditMail($schedule->getId(), 'Edit');
		}

		return $this->render('MainBundle:Schedule:schedule_confirm.html.twig', array(
			'schedule'   => $schedule
		));
	}

	/**
	* Schedule Confirm cancel after cancelling the schedule
	*
	*/
	public function scheduleConfirmCancelAction($id)
	{
		if($id == '') {
			return $this->redirect($this->generateUrl('option'));
		}
		
		$doctrine  = $this->getDoctrine()->getManager();
		$schedule  = $doctrine->getRepository('MainBundle:Schedule')->find($id);

		if($schedule->getScheduleStatus()->getId() == 1 
			|| $schedule->getScheduleStatus()->getId() == 2) {
				return $this->render('MainBundle:Schedule:schedule_confirm_cancel.html.twig', array(
					'schedule'   => $schedule
				));
		} else {
			return $this->redirect($this->generateUrl('schedule_list',array('action' => 'get')));
		}
	}

	/**
	* Common action to change the schedule status
	*
	*/
	public function statusChangeAction($action, $id)
	{
		$redirect_action 	= 'get'; 
		$ucb_patient_action = $this->container->getParameter('ucb_patient_login');
		$doctrine = $this->getDoctrine()->getManager();
		$schedule = $doctrine->getRepository('MainBundle:Schedule')->find($id);
		if($action == 'done') {
			$schedulestatus = $doctrine->getRepository('MainBundle:ScheduleStatus')->find(4);
		} else if($action == 'cancel') {
			$schedulestatus = $doctrine->getRepository('MainBundle:ScheduleStatus')->find(3);
		}
		$schedule->setScheduleStatus($schedulestatus);
		$doctrine->persist($schedule);
		$doctrine->flush();
		if($action == 'cancel') {
			$this->sendCancelledMail($schedule);
			return $this->redirect($this->generateUrl('schedule_list', array('action' => $redirect_action)));
		} else {
			return $this->redirect($ucb_patient_action);
		}
	}

	/**
	* To send the cancelled mail
	*
	*/
	private function sendCancelledMail($schedule)
	{
		$owauser = '';
		if($this->get('security.context')->getToken()->getUser() instanceof OwaUser){
			$owauser = $this->get('security.context')->getToken()->getUser();
		}

		$hcpCancelPatientMailer 	= $this->get('hcp_cancel_patient_confirmation_mailer');
		$sendHcpCancelPatientMailer = $hcpCancelPatientMailer->sendMail($schedule,$owauser);
		
		if (true !== $sendHcpCancelPatientMailer){
			throw new \Exception('Send mail exception');
		}
	}

	/**
	* To send the add/edit mail
	*
	*/
	private function sendCreateEditMail($schedule_id, $action)
	{
		$owauser = '';
		$local_timezone = $this->get('session')->get('local_timezone');
		if($this->get('security.context')->getToken()->getUser() instanceof OwaUser){
			$owauser = $this->get('security.context')->getToken()->getUser();
		}
		$doctrine  			= $this->getDoctrine()->getManager();
		$schedule  			= $doctrine->getRepository('MainBundle:Schedule')->find($schedule_id);
		$ucb_patient_action = $this->container->getParameter('ucb_patient_login');

		$patientMeetingInvite       	= $this->get('patient_meeting_invite');
		$sendPatientMeetingInvite   	= $patientMeetingInvite->send($schedule,$owauser,$local_timezone);

		if (true !== $sendPatientMeetingInvite){
			throw new \Exception('Send patient invitation exception');
		}

		if($action == 'Add') {
			$hcpConfirmationMailer       	= $this->get('hcp_confirmation_mailer');
			$sendHcpConfirmationMailer   	= $hcpConfirmationMailer->sendMail($schedule,$owauser,$ucb_patient_action);
			if (true !== $sendHcpConfirmationMailer){
				throw new \Exception('Send HCP mail exception');
			}
		}
	}

	/**
	* To update status from web service
	*
	*/
	private function updateStatusFromWebService()
	{
		$owauser = '';
		if($this->get('security.context')->getToken()->getUser() instanceof OwaUser){
			$owauser = $this->get('security.context')->getToken()->getUser();
		}

		$patientMeetingAttendee     = $this->get('patient_meeting_attendee');
		$sendPatientMeetingAttendee = $patientMeetingAttendee->send($owauser);

		if (true !== $sendPatientMeetingAttendee){
			throw new \Exception('update status exception');
		}
	}
}

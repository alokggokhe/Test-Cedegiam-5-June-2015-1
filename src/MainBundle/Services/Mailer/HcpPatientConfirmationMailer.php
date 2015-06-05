<?php

namespace MainBundle\Services\Mailer;


use Doctrine\ORM\EntityManager;
use MainBundle\Entity\Schedule;
use Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser;

class HcpPatientConfirmationMailer
{


	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $templating;

	protected $mailer;

	public function __construct(EntityManager $em, $templating, $mailer) {
		$this->em = $em;
		$this->templating = $templating;
		$this->mailer = $mailer;

	}

	/**
	 * @param Schedule $schedule MainBundle/Entity/Schedule
	 * @param OwaUser $schedule Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser
	 * @param ucb_patient_action $ucb_patient_action Dandelion Patient URL
	 * @param file_path $file_path ICS file path
	 * @param action $action Add/Edit
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function sendMail(Schedule $schedule, OwaUser $owauser, $ucb_patient_action, $action)
	{

		$subject = '';
		if($action == 'Add') {
			$subject = 'UpComing Patient Education Session - '. $schedule->getTitle();
		} else {
			$subject = 'Changes to your upcoming information session';
		}

		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom($owauser->getEmail())
			->setTo($schedule->getEmail())
			->setBody($this->templating->render('MainBundle:Mail:hcp_patient_confirmation.html.twig', array(
				'schedule' 			=> $schedule,
				'owauser' 			=> $owauser,
				'idetailling_url' 	=> $ucb_patient_action,
				'subject' 			=> $subject,
				'action' 			=> $action,
			)),'text/html');

		if($action == 'Edit') {
			$owauser_email = $owauser->getEmail();
			if($owauser_email && !empty($owauser_email)){
				$message->setCc($owauser_email);
			}
		}

		$this->mailer->send($message);

		return true;
	}
}

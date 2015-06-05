<?php

namespace MainBundle\Services\Webservice;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Doctrine\ORM\EntityManager;
use MainBundle\Entity\Schedule;
use Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser;

class PatientMeetingInvite
{


	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $ucb_meeting_invite;

	public function __construct(EntityManager $em, $ucb_meeting_invite)
	{
		$this->em = $em;
		$this->ucb_meeting_invite = $ucb_meeting_invite;
	}

	/**
	 * @param Schedule $schedule MainBundle/Entity/Schedule
	 * @param OwaUser $schedule Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser
	 */
	public function send(Schedule $schedule, OwaUser $owauser, $local_timezone)
	{
		$timezone           = new \DateTimeZone($local_timezone);
		$scheduledatetime   = new \DateTime($schedule->getScheduledatetime()->format('Y-m-d H:i:s'));
		$scheduledatetime->setTimezone($timezone);
		$offset             = str_replace('+', '', $scheduledatetime->format('P'));

		$starttime  = $schedule->getScheduledatetime()->format('Y-m-d').'T'.$schedule->getScheduledatetime()->format('H:i:s').'Z';
		$datetime   = $schedule->getScheduledatetime();
		$datetime->modify('+1 hour');
		$endtime    = $schedule->getScheduledatetime()->format('Y-m-d').'T'.$schedule->getScheduledatetime()->format('H:i:s').'Z';
		$datetime->modify('-1 hour');

		$attendees  = array();
		$details    = array();
		$webservice_request['subject']                  = $schedule->getTitle();
		$webservice_request['additionalInformation']    = '';
		$webservice_request['action']                   = 'REQUEST';
		$webservice_request['starttime']                = $starttime;
		$webservice_request['endtime']                  = $endtime;
		$webservice_request['utcOffset']                = $offset;
		$webservice_request['sessionId']                = $schedule->getId();

		// Patient attende
		$attendees[0]['inviteeId']              = $schedule->getId();
		$attendees[0]['email']                  = $schedule->getEmail();
		$attendees[0]['language']               = 'en';
		$attendees[0]['includeEnglishVersion']  = false;
		$attendees[0]['templateId']             = 'invite';
		$attendees[0]['name']                   = $schedule->getFirstname().' ' .$schedule->getLastname();
		$attendees[0]['action']                 = 'ASK';

		// details
		$details['shortcut_icon_url']       = '';
		$details['openIntoBrowserUrl']      = '';
		$details['organizerName']           = 'Dr. '. $owauser->getUsername() . ' ' . $owauser->getFirstname();
		$details['custom_message']          = '';
		$details['duration']                = '';
		$details['TokenJoinMail']           = '';
		$details['TokenJoinKey']            = '';
		$details['audio']                   = '';   
		$details['audio_conf_number']       = '';   
		$details['audio_host']              = '';   
		$details['audio_conf_id']           = '';
		$details['audio_pass']              = '';
		$details['acceptLink']              = '';
		$details['joinLink']                = '';
		$details['viewerLink']              = '';

		$webservice_request['attendees']    = $attendees;
		$webservice_request['details']      = $details;
		
		$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
		$webservice_json_request = $serializer->serialize($webservice_request, 'json');
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->ucb_meeting_invite);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $webservice_json_request);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$curl_result = curl_exec($curl);
		curl_close($curl);
		if(trim($curl_result) == 'Done!') {
			return true;
		} else {
			return false;
		}
	}
}

<?php

namespace MainBundle\Services\Webservice;

use Doctrine\ORM\EntityManager;
use MainBundle\Entity\Schedule;
use Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser;

class PatientMeetingAttendee
{


	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $ucb_meeting_attendee;

	public function __construct(EntityManager $em, $ucb_meeting_attendee)
	{
		$this->em = $em;
		$this->ucb_meeting_attendee = $ucb_meeting_attendee;
	}

	/**
	 * @param OwaUser $schedule Cegedim\Bundle\OwaCasBundle\Security\User\OwaUser
	 */
	public function send(OwaUser $owauser)
	{
		$schedule = $this->em->getRepository('MainBundle:Schedule')->getSchdules($owauser->getOnekeycode(),array(1,2,3));

		foreach ($schedule as $schedule_row) {
			$ucb_meeting_attendee_url 	= str_replace('SESSION_ID', $schedule_row->getId(), $this->ucb_meeting_attendee);
			$change_status = 0;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $ucb_meeting_attendee_url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); // Wait for connection to be made
			curl_setopt($curl, CURLOPT_TIMEOUT, 30); // Wait for response
			$curl_result = curl_exec($curl);
			curl_close($curl);
			
			$curl_json_decode = json_decode($curl_result);

			if (is_object($curl_json_decode)) {
				if(isset($curl_json_decode->attendeesUIDs[0]->status)) {
					$ical_status = $curl_json_decode->attendeesUIDs[0]->status;
					$schedule_details = $this->em->getRepository('MainBundle:Schedule')->find($schedule_row->getId());
					if($ical_status == 'DECLINED') {
						//change status to 'Cancelled'
						$schedulestatus = $this->em->getRepository('MainBundle:ScheduleStatus')->find(3);
						$change_status 	= 1;
					} else if($ical_status == 'NEEDS-ACTION' ||
								$ical_status == 'ACCEPTED' ||
								$ical_status == 'TENTATIVE' ||
								$ical_status == 'DELEGATED') {
						//change status to 'Confirmed'
						$schedulestatus = $this->em->getRepository('MainBundle:ScheduleStatus')->find(1);
						$change_status 	= 1;
					}

					if($change_status == 1) {
						$schedule_details->setScheduleStatus($schedulestatus);
						$this->em->persist($schedule_details);
						$this->em->flush();
					}
				}
			}	
		}
		return true;
	}
}

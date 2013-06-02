<?php

class BookingPage extends Page
{
}

class BookingPage_Controller extends Page_Controller
{
	static $allowed_actions = array('DateSelection', 'CourtSelection', 'TimeSelection', 'results');

	public function init()
	{
		parent::init();

		Requirements::javascript("http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js");
		Requirements::CustomScript("

            jQuery(document).ready(function()
            {
            })

        ");
	}

	static $courts = array(1 => "Court 1", 2 => "Court 2", 3 => "Court 3");

	public function ShowDateSelection()
	{
		return true;
	}

	public function DateSelection()
	{
		if ($this->ShowDateSelection())
		{
			$dateField = new DateField('SelectedDate', 'Select Date', $this->getSelectedDate());
			$dateField->setConfig('showcalendar', true);

			$fields = new FieldList($dateField);

			$actions = new FieldList(new FormAction('DateSelection_Result', 'Show details for selected date'));

			$form = new Form($this, 'DateSelection', $fields, $actions, new RequiredFields(array('SelectedDate')));
			$form->setFormAction($this->Link());

			return $form;
		}
	}

	public function ShowCourtSelection()
	{
		return ($this->ShowDateSelection() && $this->request && $this->getSelectedDate());
	}

	public function CourtSelection()
	{
		if ($this->ShowCourtSelection())
		{
			$courtField = new DropdownField("SelectedCourt", "Select Court", BookingPage_Controller::$courts, $this->getSelectedCourt());

			$fields = new FieldList($courtField);

			$actions = new FieldList(new FormAction($this->Link(), 'Show booking sheet for selected court'));

			$form = new Form($this, 'CourtSelection', $fields, $actions);

			$form->setFormAction($this->Link());

			return $form;
		}
	}

	public function ShowTimeSelection()
	{
		return ($this->ShowCourtSelection() && $this->request && $this->getSelectedCourt());
	}

	public function TimeSelection()
	{
		if ($this->ShowTimeSelection())
		{
			$begin = new DateTime($this->getSelectedDate());
			$begin->setTimezone(new DateTimeZone("Australia/Sydney"));
			$begin->setTime(0, 0, 0);

			$end = new DateTime($this->getSelectedDate());
			$end->setTimezone(new DateTimeZone("Australia/Sydney"));
			$end->add(DateInterval::createFromDateString("1 day"));

			$fields = new FieldList();

			$interval = DateInterval::createFromDateString("+30 minutes");
			$period = new DatePeriod($begin, $interval, $end);

			$date = new DateTime($this->getSelectedDate());
			$date->setTimezone(new DateTimeZone("Australia/Sydney"));
			$date->setTime(0, 0, 0);
			$bookingsQ = Booking::get()->filter(array('Court' => $this->getSelectedCourt(), 'Date' => $date->format('Y-m-d')))->sort('StartSlot');
			$i = 0;
			$bookings = array();
			$booking = null;

			foreach($bookingsQ as $bookingQ)
			{
				array_push($bookings, $bookingQ);
				if ($booking == null)
				{
					$booking = $bookingQ;
				}
			}

			foreach ( $period as $dt )
			{
				$field = new CheckboxField("TimeSlot" . $dt->format("H:i"), $dt->format("H:i"));

				if ($booking != null)
				{
					if (strtotime($booking->StartSlot) <= strtotime($dt->format("H:i")))
					{
						$field->setAttribute('disabled', 'true');
					}

					if (strtotime($dt->format("H:i")) >= strtotime($booking->EndSlot))
					{
						$booking = null;
						$i++;

						if (count($bookings) > $i)
						{
							$booking = $bookings[$i];
						}
					}
				}

				$fields->push($field);
			}

			$actions = new FieldList(new FormAction($this->Link("results"), 'Book time'));
			$form = new Form($this, 'TimeSelection', $fields, $actions);

			$form->setFormAction($this->Link('results'));

			return $form;
		}
	}

	function getProperty(string $varName)
	{
		if ($this->request && $this->request->postVar($varName))
		{
			$varVal = $this->request->postVar($varName);
			$_SESSION[$varName] = $varVal;
			return $varVal;
		}
		else if (isset($_SESSION[$varName]))
		{
			return $_SESSION[$varName];
		}
	}

	function getSelectedDate()
	{
		return $this->getProperty('SelectedDate');
	}

	function getSelectedCourt()
	{
		return $this->getProperty('SelectedCourt');
	}

	function getSelectedStartTime()
	{
		return $this->getProperty('SelectedStartTime');
	}

	function getSelectedEndTime()
	{
		return $this->getProperty('SelectedEndTime');
	}

	function clearSessionData()
	{
		$this->clearSessionVar('SelectedDate');
		$this->clearSessionVar('SelectedCourt');
		$this->clearSessionVar('SelectedStartTime');
		$this->clearSessionVar('SelectedEndTime');
	}

	function clearSessionVar($varName)
	{
		if (isset($_SESSION[$varName]))
		{
			unset($_SESSION[$varName]);
		}
	}

	function results($data)
	{
		$times = $data->postVars();

		$startTime = null;
		$endTime = null;

		foreach ($times as $key => $time)
		{
			if (strpos($key, "TimeSlot") === 0)
			{
				$time = str_replace('TimeSlot', '', $key);

				if ($startTime == null)
				{
					$startTime = $time;
				}

				$endTime = $time;
			}
		}

		$booking = new Booking();
		$booking->Court = $this->getSelectedCourt();
		$booking->Date = $this->getSelectedDate();
		$booking->StartSlot = $startTime;
		$booking->EndSlot = $endTime;

		$result = 'fail';
		if ($this->canBookTime($booking))
		{
			$result = 'success';
			$booking->write();
		}

		$data = array(
			'BookingResults' => $result,
			'Title' => 'Booking Result'
		);

		$this->clearSessionData();

		return $this->owner->customise($data)->renderWith(array('Page_Results', 'Page'));
	}

	function canBookTime($booking)
	{
		$startTime = strtotime($booking->StartSlot);
		$endTime = strtotime($booking->EndSlot);

		$date = new DateTime($booking->Date);
		$bookings = Booking::get()->filter(array('Court' => $booking->Court, 'Date' => $date->format('Y-m-d')))->sort('StartSlot');

		foreach($bookings as $existing)
		{
			$existingStartTime = strtotime($existing->StartSlot);
			$existingEndTime = strtotime($existing->EndSlot);

			if ($endTime < $existingStartTime)
			{
				continue;
			}

			if ($startTime > $existingEndTime)
			{
				continue;
			}

			return false;
		}

		return true;
	}
}


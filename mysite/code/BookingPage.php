<?php

class BookingPage extends Page
{
}

class BookingPage_Controller extends Page_Controller
{
	static $allowed_actions = array(
		'DateSelection',
		'DateSelection_Result',
		'CourtSelection',
		'CourtSelection_Result',
		'TimeSelection',
		'TimeSelection_Result',
		'results'
	);

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

	function CreateForm($name, $fields, $action, $validation, $clear)
	{
		$formAction = null;
		$fieldList = new FieldList();

		if ($clear)
		{
			$formAction = $name . '_Clear';
			$action = 'Clear';
			$validation = null;

			foreach($fields as $field)
			{
				$field = $field->performReadonlyTransformation();
				$fieldList->push($field);
			}
		}
		else
		{
			$formAction = $name . '_Result';
			foreach($fields as $field)
			{
				$fieldList->push($field);
			}
		}

		$actions = FieldList::create(FormAction::create($formAction, $action));

		return new Form($this, $name, $fieldList, $actions, $validation);
	}

	public function DateSelection()
	{
		if ($this->ShowDateSelection())
		{
			$fields = array(DateField::create('SelectedDate', 'Select Date', $this->getSelectedDate())->setConfig('showcalendar', true));
			$requiredFields = RequiredFields::create(array('SelectedDate'));
			$action = 'Show details for date';
			$form = $this->CreateForm('DateSelection', $fields, $action, $requiredFields, ($this->getSelectedDate() != null));

			return $form;
		}
	}

	function DateSelection_Result($data, $form)
	{
		$selectedDate = strtotime($data['SelectedDate']);
		$now = strToTime('today');

		if ($selectedDate < $now)
		{
			$form->addErrorMessage('SelectedDate', 'Please select a future date', 'bad');
			return $this->DateSelection_Clear(null, null);
		}

		return $this->redirectBack();
	}

	function DateSelection_Clear($data, $form)
	{
		$this->clearSessionVar('SelectedDate');
		$this->clearSessionVar('SelectedCourt');
		$this->clearSessionVar('SelectedStartTime');
		$this->clearSessionVar('SelectedEndTime');

		return $this->redirectBack();
	}

	public function ShowCourtSelection()
	{
		return ($this->ShowDateSelection() && $this->request && $this->getSelectedDate());
	}

	public function CourtSelection()
	{
		if ($this->ShowCourtSelection())
		{
			$fields = FieldList::create(DropdownField::create("SelectedCourt", "Select Court", BookingPage_Controller::$courts, $this->getSelectedCourt()));
			$action = 'Show booking sheet for selected court';
			$form = $this->createForm('CourtSelection', $fields, $action, null, ($this->getSelectedCourt() != null));

			return $form;
		}
	}

	function CourtSelection_Result($data, $form)
	{
		return $this->redirectBack();
	}

	function CourtSelection_Clear($data, $form)
	{
		$this->clearSessionVar('SelectedCourt');
		$this->clearSessionVar('SelectedStartTime');
		$this->clearSessionVar('SelectedEndTime');

		return $this->redirectBack();
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

			$action = 'Book time';
			$form = $this->createForm('TimeSelection', $fields, $action, null, false);

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

		return null;
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

	function TimeSelection_Result($data, $form)
	{
		$times = $data;

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

		if ($startTime == null || $endTime == null)
		{
			$form->sessionMessage("Please select a time", 'bad');
			return $this->redirectBack();
		}

		$booking = new Booking();
		$booking->MemberID = Member::currentUserID();
		$booking->Court = $this->getSelectedCourt();
		$booking->Date = $this->getSelectedDate();
		$booking->StartSlot = $startTime;
		$booking->EndSlot = $endTime;

		if (!$this->canBookTime($booking))
		{
			$form->sessionMessage("Please select a time", 'bad');
			return $this->redirectBack();
		}

		$datetime = new DateTime($this->getSelectedDate());

		Debug::show($startTime);
		$dateVal = $datetime->getTimestamp();
		$result = date('D, d M Y ', $dateVal) . ' at '. $startTime .' Court ' . $booking->Court;

		$data = array(
			'BookingResults' => $result,
			'Title' => 'Booking Result'
		);

		$this->clearSessionData();

		$booking->write();

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


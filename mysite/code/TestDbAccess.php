<?php

class TestDbAccessPage extends Page
{
}

class TestDbAccessPage_Controller extends Page_Controller
{
	static $allowed_actions = array('TestDbAccess', 'TestDbAccess_Test');

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

	public function TestDbAccess_Test()
	{
		if ($this->getSelectedDate())
		{
			return '<div>' . $this->getSelectedDate() . '</div>';
		}
	}

	public function TestDbAccess()
	{
		//Debug::show($_SESSION);
		if (isset($_SESSION))
		{
			//Debug::show($_SESSION);
			if (isset($_SESSION['FormInfo']))
			{
				Debug::show($_SESSION['FormInfo']);
			}

//			if (isset($_SESSION['FormInfo']['SearchForm_TestDbAccess']['errors']))
//			{
//				unset($_SESSION['FormInfo']['SearchForm_TestDbAccess']['errors']);
//			}
		}

		$dateField = new DateField('selectedDate', 'Select Date', $this->getSelectedDate());
		$dateField->setConfig('showcalendar', true);

		$fields = new FieldList($dateField);

		$actions = new FieldList(new FormAction($this->Link(), 'Go'));

		$form = new Form($this, 'TestDbAccess', $fields, $actions);

		$form->setFormAction($this->Link());

		return $form;
	}

	function getSelectedDate()
	{
		if ($this->request)
		{
			return $this->request->postVar("selectedDate");
		}
	}
}


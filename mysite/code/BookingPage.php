<?php

class BookingPage extends SiteTree {
}

class BookingPage_Controller extends Page_Controller {
	function Form(){
		return new BookingForm($this, 'Form');
	}
}




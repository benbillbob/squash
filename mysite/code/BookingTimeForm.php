<?php

class BookingTimeForm extends MultiFormStep
{
	public static $is_final_step = true;

	function getFields()
	{
		return new FieldSet
		(
			new TextField('BookingTime', 'Booking Time'), 
			new TextField('BookingLength', 'Booking Length')
		);
	}
}

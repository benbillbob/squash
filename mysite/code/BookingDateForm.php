<?php

class BookingDateForm extends MultiFormStep
{
    public static $next_steps = 'BookingTimeForm';

    function getFields()
    {
        $dateField = new DateField_View_JQuery('BookingDate', 'Booking Date');
        showcalendar
		return new FieldSet(
            $dateField
        );
	}
}

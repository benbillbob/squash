<?php

class BookingDateForm extends MultiFormStep
{
    public static $next_steps = 'BookingTimeForm';

    function getFields()
    {
        $dateField = new DateField('BookingDate', 'Booking Date');
        $dateField . setConfig('showcalendar');
        return new FieldSet(
            $dateField
        );
    }
}

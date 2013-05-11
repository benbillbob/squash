<?php

class BookingDateForm extends MultiFormStep
{
    public static $next_steps = 'BookingTimeForm';

    function getFields()
    {
        return new FieldSet(
            new DateField('BookingDate', 'Booking Date')
        );
    }
}

<?php

class BookingDateForm extends MultiFormStep
{
    public static $next_steps = 'BookingTimeForm';

    function getFields()
    {
        $file = 'people.log';
        $person = "John Smith\n";
        file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
        return new FieldSet(
            new DateField('BookingDate', 'Booking Date')
        );
    }
}

<?php

class BookingTimeForm extends MultiFormStep
{
    public static $is_final_step = true;

    function getFields()
    {
        return new FieldSet
        (
            new TimeField('BookingTime', 'Booking Time'),
            new NumericField('BookingLength', 'Booking Length')
        );
    }
}

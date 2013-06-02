<?php

class Booking extends DataObject
{
    static $db = array(
		'Court' => 'Varchar',
		'Date' => 'Date',
        'StartSlot' => 'Time',
        'EndSlot' => 'Time');
	static $has_one = array(
		'Member' => 'Member'
	);
}

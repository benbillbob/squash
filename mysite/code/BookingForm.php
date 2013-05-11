<?php

class BookingForm extends MultiForm
{
    public static $start_step = 'BookingDateForm';

    public function finish($data, $form)
    {
        parent::finish($data, $form);
        $steps = DataObject::get('MultiFormStep', "SessionID = {$this->session->ID}");
        if ($steps) {
            foreach ($steps as $step) {
                //Debug::show($step->loadData());
            }
        }

        return array(
            'Title' => 'Thank you for your submission',
            'Content' => 'You have successfully submitted the form. Thanks!'
        );
    }
}

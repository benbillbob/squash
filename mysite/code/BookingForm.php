<?php

class BookingForm extends MultiForm
{
    public static $start_step = 'BookingDateForm';

    public function finish($data, $form)
    {
        parent::finish($data, $form);
        $steps = DataObject::get('MultiFormStep', "SessionID = {$this->session->ID}");
        $message = "finished<br>";
        if ($steps) {
            foreach ($steps as $step) {
                $message = $message . '<br>' . $step->loadData() . '<br>';
            }
        }

        return $this->controller->customise(array('Form' => false, 'Content' => $message))->renderWith('Page');
    }
}

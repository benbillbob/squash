<?php

class BookingForm extends MultiForm
{
    public static $start_step = 'BookingDateForm';

    public function finish($data, $form)
    {
        parent::finish($data, $form);
        $message = "finished<br>";

        $steps = DataObject::get('MultiFormStep', "SessionID = {$this->session->ID}");

        if ($steps) {
            foreach ($steps as $step) {
                $arr = $step->loadData();
                foreach ($arr as $key => $value) {
                    $message = $message . '<br>' . $key . ':' . $value . '<br>';
                }
            }
        }

        $booking = new Booking();
        $booking->Date = '421';
        $booking->Time = '5432';
        $booking->Length = '4532';

        $booking->write();

        $this->session->delete();

        return $this->controller->customise(array('Form' => false, 'Content' => $message))->renderWith('Page');
    }
}

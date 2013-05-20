<?php

class BookingForm extends MultiForm
{
    public static $start_step = 'BookingDateForm';

    public function finish($data, $form)
    {
        parent::finish($data, $form);
        $message = "finished<br>";

        $steps = DataObject::get('MultiFormStep', "SessionID = {$this->session->ID}");

        $formData = array();

        if ($steps) {
            foreach ($steps as $step) {
                $arr = $step->loadData();
                $formData = array_merge($formData, $arr);
            }
        }
        {
            //user_error("No Data", )
        }
        /*
         * BookingDate:2013-05-01

        BookingTime:dfg

        BookingLength:cvb
         */
        $booking = new Booking();
        $booking->Date = $formData['BookingDate'];
        $booking->Time = $formData['BookingTime'];
        $booking->Length = $formData['BookingLength'];

        Debug::show($formData['BookingDate']);
        Debug::show($formData['BookingTime']);
        Debug::show($formData['BookingLength']);
        Debug::show($booking->Date);
        Debug::show($booking->Time);
        Debug::show($booking->Length);

        $booking->write();

        $this->session->delete();

        return $this->controller->customise(array('Form' => false, 'Content' => $message))->renderWith('Page');
    }
}

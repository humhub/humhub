<?php
class LogoWidget extends HWidget {

    public $place = 'topMenu';

    public function run() {

        $this->render('logo', array('logo' => new LogoImage(), 'place' => $this->place));
    
    }

}

?>
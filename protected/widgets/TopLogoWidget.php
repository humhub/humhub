<?php
class TopLogoWidget extends HWidget {

    
    public function run() {
        // render heditor view
        $this->render('topLogo', array('logo' => new LogoImage()));
    
    }

}

?>
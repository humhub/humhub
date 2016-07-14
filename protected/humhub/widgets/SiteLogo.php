<?php

namespace humhub\widgets;

use humhub\libs\LogoImage;

class SiteLogo extends \yii\base\Widget
{

    public $place = 'topMenu';

    public function run()
    {

        return $this->render('logo', array('logo' => new LogoImage(), 'place' => $this->place));
    }

}

?>
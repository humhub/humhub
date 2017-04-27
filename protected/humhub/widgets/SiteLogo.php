<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\libs\LogoImage;

class SiteLogo extends \yii\base\Widget
{

    public $place = 'topMenu';

    public function run()
    {

        return $this->render('logo', [
            'logo' => new LogoImage(),
            'place' => $this->place
        ]);
    }

}

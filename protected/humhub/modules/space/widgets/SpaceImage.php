<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use \yii\base\Widget;

/**
 * Return space image or acronym
 */
class SpaceImage extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;
    public $acronymCount = 2;
    public $width = 140;
    public $height = 140;
    public $cssImageClass = "";
    public $cssAcronymClass = "profile-image";

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('spaceImage', [
            'space' => $this->space,
            'acronym' => $this->getAcronym(),
            'width' => $this->width,
            'height' => $this->height,
            'cssImageClass' => $this->cssImageClass,
            'cssAcronymClass' => $this->cssAcronymClass,
        ]);
    }


    protected function getAcronym()
    {
        $words = explode(" ", strtoupper($this->space->name));
        $acronym = "";

        foreach ($words as $w) {
            $acronym .= $w[0];
        }

        $acronym = substr ($acronym , 0, $this->acronymCount);

        return $acronym;
    }

}

?>
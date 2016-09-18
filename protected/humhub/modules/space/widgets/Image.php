<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use \yii\base\Widget;
use yii\bootstrap\Html;

/**
 * Return space image or acronym
 */
class Image extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;
    public $acronymCount = 2;
    public $width;
    public $height;
    public $htmlOptions = [];
    public $link = false;
    public $linkOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->height) {
            $this->height = $this->width;
        }

        if (!isset($this->linkOptions['href'])) {
            $this->linkOptions['href'] = $this->space->getUrl();
        }

        if ($this->space->color != null) {
            $color = Html::encode($this->space->color);
            ;
        } else {
            $color = '#d7d7d7';
        }

        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = "";
        }

        if (!isset($this->htmlOptions['style'])) {
            $this->htmlOptions['style'] = "";
        }

        $acronymHtmlOptions = $this->htmlOptions;
        $imageHtmlOptions = $this->htmlOptions;



        $acronymHtmlOptions['class'] .= " space-profile-acronym-" . $this->space->id . " space-acronym";
        $acronymHtmlOptions['style'] .= " background-color: " . $color . "; width: " . $this->width . "px; height: " . $this->height . "px;";
        $acronymHtmlOptions['style'] .= " " . $this->getDynamicStyles($this->width);

        $imageHtmlOptions['class'] .= " space-profile-image-" . $this->space->id . " img-rounded profile-user-photo";
        $imageHtmlOptions['style'] .= " width: " . $this->width . "px; height: " . $this->height . "px";
        $imageHtmlOptions['alt'] = Html::encode($this->space->name);

        $defaultImage = (basename($this->space->getProfileImage()->getUrl()) == 'default_space.jpg' || basename($this->space->getProfileImage()->getUrl()) == 'default_space.jpg?cacheId=0') ? true : false;

        if (!$defaultImage) {
            $acronymHtmlOptions['class'] .= " hidden";
        } else {
            $imageHtmlOptions['class'] .= " hidden";
        }

        return $this->render('image', [
                    'space' => $this->space,
                    'acronym' => $this->getAcronym(),
                    'link' => $this->link,
                    'linkOptions' => $this->linkOptions,
                    'acronymHtmlOptions' => $acronymHtmlOptions,
                    'imageHtmlOptions' => $imageHtmlOptions,
        ]);
    }

    protected function getAcronym()
    {
        $acronym = "";

        foreach (explode(" ", $this->space->name) as $w) {
            if (mb_strlen($w) >= 1) {
                $acronym .= mb_substr($w, 0, 1);
            }
        }

        return mb_substr(mb_strtoupper($acronym), 0, $this->acronymCount);
    }

    protected function getDynamicStyles($elementWidth)
    {

        $fontSize = 44 * $elementWidth / 100;
        $padding = 18 * $elementWidth / 100;
        $borderRadius = 4;

        if ($elementWidth < 140 && $elementWidth > 40) {
            $borderRadius = 3;
        }

        if ($elementWidth < 35) {
            $borderRadius = 2;
        }

        return "font-size: " . $fontSize . "px; padding: " . $padding . "px 0; border-radius: " . $borderRadius . "px;";
    }

}

?>
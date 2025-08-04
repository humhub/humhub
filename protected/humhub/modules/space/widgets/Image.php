<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\ui\widgets\BaseImage;

/**
 * Return space image or acronym
 */
class Image extends BaseImage
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @var int number of characters used in the acronym
     */
    public $acronymCount = 2;


    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!isset($this->linkOptions['href'])) {
            $this->linkOptions['href'] = $this->space->getUrl();
        }

        if ($this->space->color != null) {
            $color = Html::encode($this->space->color);
        } else {
            $color = 'var(--background3)';
        }

        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = '';
        }

        if (!isset($this->htmlOptions['style'])) {
            $this->htmlOptions['style'] = '';
        }

        $acronymHtmlOptions = $this->htmlOptions;
        $imageHtmlOptions = $this->htmlOptions;

        $acronymHtmlOptions['class'] .= " space-profile-acronym-" . $this->space->id . " space-acronym";
        $acronymHtmlOptions['style'] .= " background-color: " . $color . "; width: " . $this->width . "px; height: " . $this->height . "px;";
        $acronymHtmlOptions['style'] .= " " . $this->getDynamicStyles($this->width);
        $acronymHtmlOptions['data-contentcontainer-id'] = $this->space->contentcontainer_id;

        $imageHtmlOptions['class'] .= " space-profile-image-" . $this->space->id . " rounded profile-user-photo";
        $imageHtmlOptions['style'] .= " width: " . $this->width . "px; height: " . $this->height . "px;";
        $imageHtmlOptions['alt'] = Html::encode($this->space->name);

        $imageHtmlOptions['data-contentcontainer-id'] = $this->space->contentcontainer_id;

        if ($this->showTooltip) {
            $this->htmlOptions['data-bs-toggle'] = 'tooltip';
            $this->htmlOptions['data-placement'] = 'top';
            $this->htmlOptions['data-html'] = 'true';
            $this->htmlOptions['data-bs-title'] = $this->tooltipText ?: Html::encode($this->space->name);
            Html::addCssClass($this->htmlOptions, 'tt');
        }

        $isDefaultImage = str_starts_with(basename($this->space->getProfileImage()->getUrl()), 'default_space.jpg');

        if ($isDefaultImage) {
            $imageHtmlOptions['class'] .= ' d-none-space-image'; // Don't replace with `d-none` because it would be removed after changing space
        } else {
            $acronymHtmlOptions['class'] .= ' d-none-space-image'; // Idem
        }

        return $this->render('@space/widgets/views/image', [
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
        $acronym = '';

        $spaceName = preg_replace('/[^\p{L}\d\s]+/u', '', $this->space->name);

        foreach (explode(' ', (string) $spaceName) as $word) {
            if (mb_strlen($word) >= 1) {
                $acronym .= mb_substr($word, 0, 1);
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

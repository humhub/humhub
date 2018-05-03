<?php

namespace humhub\modules\content\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Generic WallEntryControlLink.
 *
 * @since 1.2
 * @author buddh4
 */
class WallEntryControlLink extends \humhub\components\Widget
{

    /**
     * Object derived from HActiveRecordContent
     *
     * @var string
     */
    public $label;

    /**
     * Object derived from HActiveRecordContent
     *
     * @var string
     */
    public $icon;

    /**
     *
     * @var array
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if(empty($this->getLabel())) {
            $this->label = ArrayHelper::remove($this->options, 'label', 'Label');
        }

        ArrayHelper::remove($this->options, 'sortOrder');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return '<li>'.$this->renderLink().'</li>';
    }

    /**
     * @return string link label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string link icon
     */
    public function getIcon()
    {
        if(empty($this->icon)) {
            $this->icon = ArrayHelper::remove($this->options, 'icon');
        }

        return $this->icon;
    }

    /**
     * @return string renders the actual link
     */
    protected function renderLink()
    {
        return Html::a($this->renderLinkText(), '#', $this->options);
    }

    /**
     * @return string renders the link text with icon
     */
    protected function renderLinkText()
    {
        return ($this->icon) ? '<i class="fa '.$this->getIcon().'"></i> '.$this->getLabel() : $this->getLabel();
    }

}

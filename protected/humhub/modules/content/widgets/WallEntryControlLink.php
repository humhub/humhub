<?php

namespace humhub\modules\content\widgets;

use humhub\components\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Generic WallEntryControlLink.
 *
 * @since 1.2
 * @author buddh4
 */
class WallEntryControlLink extends Widget
{

    /**
     * @var string link label
     */
    public $label;

    /**
     * @var string link action
     */
    public $action;

    /**
     * @var string link action-url
     */
    public $actionUrl;

    /**
     * Object derived from HActiveRecordContent
     *
     * @var string
     */
    public $icon;

    /**
     *
     * @var [] link html options
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

        if(!empty($this->getAction())) {
            $this->options['data-action-click'] = $this->getAction();
        }

        if(!empty($this->getActionUrl())) {
            $this->options['data-action-url'] = $this->getActionUrl();
        }

        ArrayHelper::remove($this->options, 'sortOrder');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if($this->preventRender()) {
            return '';
        }

        return '<li>'.$this->renderLink().'</li>';
    }

    /**
     * This function may contain validation logic as permission checks.
     *
     * @return bool true if this link should be rendered false if not
     */
    public function preventRender()
    {
        return false;
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
     * @return string|null action url
     * @since 1.3
     */
    public function getActionUrl()
    {
        return null;
    }

    /**
     * @return string|null link action
     * @since 1.3
     */
    private function getAction()
    {
        return $this->action;
    }

}

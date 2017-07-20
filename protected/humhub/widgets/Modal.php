<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * AjaxButton is an replacement for Yii1 CHtml::AjaxButton
 *
 * @author luke
 */
class Modal extends JsWidget
{   
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.modal.Modal';

    /**
     * Header text
     * @var string
     */
    public $header;

    /**
     * Modal content
     * @var string
     */
    public $body;

    /**
     * Modal footer
     * @var string
     */
    public $footer;

    /**
     * This setting will have impact on on the modal dialog size.
     * Possible values:
     *  - normal
     *  - large
     *  - small
     *  - extra-small
     *  - medium
     * @var string
     */
    public $size;

    /**
     * Can be used to add an open animation for the dialog.
     * e.g: pulse
     *
     * @var string
     */
    public $animation;

    /**
     * Can be set to true to force the x close button to be rendered even if
     * there is no headtext available, or set to false if the button should not
     * be rendered.
     *
     * @var boolean
     */
    public $showClose;

    /**
     * If set to false $backdrop and §keyboard will be set to false automaticly, so
     * the modal is only closable by buttons.
     *
     * @var bool
     */
    public $closable = true;

    /**
     * Defines if a click on the modal background should close the modal
     * @var boolean
     */
    public $backdrop = true;

    /**
     * Defines if the modal can be closed by pressing escape
     * @var boolean
     */
    public $keyboard = true;

    /**
     * Defines if the modal should be shown at startup
     * @var boolean
     */
    public $show = false;

    /**
     * Defines if the modal should be shown at startup
     * @var boolean
     */
    public $centerText = false;

    /**
     * Can be set to false if the modal body should not be initialized with an
     * loader animation. Default is true, if no body is provided.
     *
     * @var boolean
     */
    public $initialLoader;

    public function run()
    {
        return $this->render('modal', [
            'id' => $this->id,
            'options' => $this->getOptions(),
            'header' => $this->header,
            'body' => $this->body,
            'footer' => $this->footer,
            'animation' => $this->animation,
            'size' => $this->size,
            'centerText' => $this->centerText,
            'initialLoader' => $this->initialLoader
        ]);
    }

    public function getAttributes()
    {
        return [
            'class' => "modal",
            'tabindex' => "-1",
            'role' => "dialog",
            'aria-hidden' => "true"
        ];
    }

    public function getData()
    {
        $result = [];

        if(!$this->closable || !$this->backdrop) {
            $result['backdrop'] = 'static';
        }

        if(!$this->closable || !$this->keyboard) {
            $result['keyboard'] = 'false';
        }

        if($this->show) {
            $result['show'] = 'true';
        }

        return $result;
    }

}

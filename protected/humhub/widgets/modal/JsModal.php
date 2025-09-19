<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\modal;

use humhub\widgets\JsWidget;

/**
 * Usage examples:
 *
 * ```
 * <?= JsModal::widget([
 *   'id' => 'myModal',
 *   'jsWidget' => 'myJsWidget',
 * ]) ?>
 * ```
 */
class JsModal extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.modal.Modal';

    /**
     * @var string the title content in the modal window.
     * @since since 1.18.0
     */
    public $title;

    /**
     * @deprecated since 1.18.0 use [[title]] instead
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
     * @var string|null the modal size.
     * Possible values (see https://getbootstrap.com/docs/5.3/components/modal/#optional-sizes):
     * - Empty for default.
     * - \humhub\widgets\modal\Modal::SIZE_SMALL
     * - \humhub\widgets\modal\Modal::SIZE_LARGE
     * - \humhub\widgets\modal\Modal::SIZE_EXTRA_LARGE
     *
     * Deprecated values:
     *  - extra-small
     *  - small
     *  - normal
     *  - medium
     *  - large
     * @var string
     */
    public $size;

    /**
     * If set to false $backdrop and §keyboard will be set to false automaticly, so
     * the modal is only closable by buttons.
     *
     * @var bool
     */
    public $closable = true;

    /**
     * Defines if a click on the modal background should close the modal
     * @var bool
     */
    public $backdrop = true;

    /**
     * Defines if the modal can be closed by pressing escape
     * @var bool
     */
    public $keyboard = true;

    /**
     * Defines if the modal should be shown at startup
     * @var bool
     */
    public $show = false;

    /**
     * @deprecated since 1.18.0 (all modal boxes are opened with the fade animation)
     */
    public $animation;

    /**
     * @deprecated since 1.18.0 use [[Modal::closeButton]] instead
     */
    public $showClose;

    /**
     * @deprecated since 1.18.0
     */
    public $centerText = false;

    /**
     * Can be set to false if the modal body should not be initialized with an
     * loader animation. Default is true, if no body is provided.
     *
     * @var bool
     */
    public $initialLoader;

    public function run()
    {
        return $this->render('@humhub/widgets/modal/views/modal', [
            'options' => $this->getOptions(),
            'title' => $this->title,
            'body' => $this->body,
            'footer' => $this->footer,
            'size' => $this->size,
            'closable' => $this->closable,
            'backdrop' => $this->backdrop,
            'keyboard' => $this->keyboard,
            'show' => $this->show,
            'showClose' => $this->showClose,
            'initialLoader' => $this->initialLoader,
        ]);
    }

    public function getAttributes()
    {
        return [
            'aria-hidden' => "true",
        ];
    }
}

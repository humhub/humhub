<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\modal;

use humhub\widgets\form\ActiveForm;
use yii\bootstrap5\Html;

/**
 * Provides an extension of the yii\bootstrap5\Modal class with additional features.
 *
 * Usages:
 *
 *  ~~~php
 * <?php Modal::beginDialog([
 *     'title' => Yii::t('ModuleIdModule.base', 'Title'),
 *     'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
 * ]) ?>
 *     Content
 * <?php Modal::endDialog() ?>
 *  ~~~
 *
 * ~~~php
 * <?php $form = Modal::beginFormDialog([
 *     'title' => Yii::t('ModuleIdModule.base', 'Title'),
 *     'footer' => ModalButton::cancel() . ' ' . ModalButton::save()->submit(),
 *     'form' => [], //  configuration for the form (optional)
 * ]) ?>
 *     Content and the form inputs for $form
 * <?php Modal::endFormDialog()?>
 * ~~~
 *
 * ~~~php
 * Modal::widget([
 *     'title' => Yii::t('ModuleIdModule.base', 'Title'),
 *     'body' => 'Content',
 *     'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
 * ])
 * ~~~
 *
 * @since 1.18
 * @see https://getbootstrap.com/docs/5.3/components/modal/
 */
class Modal extends \yii\bootstrap5\Modal
{
    /**
     * Defines if a click on the modal background should close the modal
     * It's false because it's often used for serious work, for example html forms,
     * accidental closing of which can lead to loss of user data.
     */
    public $backdrop = false;

    /**
     * Defines if the modal can be closed by pressing escape
     */
    public bool $keyboard = true;

    /**
     * If set to false $backdrop and §keyboard will be considered as false,
     * so the modal is only closable by buttons
     */
    public bool $closable = true;

    /**
     * Defines if the modal should be shown at startup
     */
    public bool $show = false;

    /**
     * @deprecated since 1.18.0 use [[closeButton]] instead
     */
    public $showClose;

    /**
     * @deprecated since 1.18.0 use [[title]] instead
     */
    public $header;

    /**
     * @deprecated since 1.18.0 (all modal boxes are opened with the fade animation)
     */
    public $animation;

    /**
     * @deprecated since 1.18.0
     */
    public $centerText;

    /**
     * @var string Body text, useful when this widget is called as Modal::widget(['body' => '...'])
     */
    public string $body = '';

    /**
     * If true, removes the Widget div wrapper
     */
    public bool $isHumHubDialog = false;

    /**
     * @var self the Modal that are currently being rendered (not ended). This property
     * is maintained by [[beginDialog()]] and [[endDialog()]] methods.
     * @internal
     */
    public static self $stackForDialog;

    protected function initOptions()
    {
        $this->options['data-bs-backdrop'] = ($this->closable && $this->backdrop) ? 'true' : 'static';
        $this->options['data-bs-keyboard'] = ($this->closable && $this->keyboard) ? 'true' : 'false';
        // Disable autofocus on click outside the modal to avoid issue with Select2 rendered outside
        $this->options['data-bs-focus'] = 'false';

        $this->clientOptions['show'] = $this->show;

        // TODO: remove in later version
        $this->title = $this->title ?: $this->header;
        if ($this->showClose === false) {
            $this->closeButton = false;
        }
        // Convert size from deprecated values to new ones
        if ($this->size === 'extra-small') {
            $this->size = static::SIZE_SMALL;
        } elseif ($this->size === 'small') {
            $this->size = static::SIZE_DEFAULT;
        } elseif ($this->size === 'normal') {
            $this->size = static::SIZE_DEFAULT;
        } elseif ($this->size === 'medium') {
            $this->size = static::SIZE_LARGE;
        } elseif ($this->size === 'large') {
            $this->size = static::SIZE_EXTRA_LARGE;
        }

        parent::initOptions();
    }

    public function run()
    {
        if ($this->isHumHubDialog === true) {
            echo $this->renderDialogBegin() . "\n" .
                $this->renderHeader() . "\n" .
                $this->renderBodyBegin() . "\n" .
                $this->body . "\n" .
                $this->renderBodyEnd() . "\n" .
                $this->renderFooter() . "\n" .
                $this->renderDialogEnd();
            $this->registerPlugin('modal');
        } else {
            parent::run();
        }
    }

    public static function widget($config = [])
    {
        $config['isHumHubDialog'] = true;
        return parent::widget($config);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        if ($this->isHumHubDialog) {
            $this->trigger(self::EVENT_INIT);
            if (!isset($this->options['id'])) {
                $this->options['id'] = $this->getId();
            }
            $this->initOptions();
        } else {
            parent::init();
        }
    }

    public function renderDialogBegin(): string
    {
        // Set dialog backdrop and keyboard options to update modal options via humhub.ui.modal.js
        $this->dialogOptions['data-hh-backdrop'] = ($this->closable && $this->backdrop) ? 'true' : 'static';
        $this->dialogOptions['data-hh-keyboard'] = ($this->closable && $this->keyboard) ? 'true' : 'false';

        return
            Html::beginTag('div', $this->dialogOptions) . "\n" .
            Html::beginTag('div', ['class' => 'modal-content']) . "\n";
    }

    public function renderDialogEnd(): string
    {
        return
            "\n" . Html::endTag('div') . // modal-content
            "\n" . Html::endTag('div'); // modal-dialog
    }

    /**
     * Initializes and begins rendering the dialog part of the modal.
     *
     * Use case example:
     *
     *   ```
     *   <?php Modal::beginDialog([
     *     'title' => Yii::t('ModuleIdModule.base', 'Title'),
     *     'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
     *     'id' => 'modal-custom-id',
     *     'size' => Modal::SIZE_LARGE,
     *   ]) ?>
     *       Content
     *   <?php Modal::endDialog() ?>
     *   ```
     */
    public static function beginDialog($config = [], bool $renderElements = true): void
    {
        $config['isHumHubDialog'] = true;
        $widget = new static($config);
        self::$stackForDialog = $widget;
        echo $widget->renderDialogBegin();
        if ($renderElements) {
            echo
                $widget->renderHeader() . "\n" .
                $widget->renderBodyBegin() . "\n";
        }
    }

    /**
     * Ends rendering the dialog part of the modal.
     */
    public static function endDialog(bool $renderElements = true): void
    {
        $widget = self::$stackForDialog;
        if ($widget) {
            if ($renderElements) {
                echo
                    "\n" . $widget->renderBodyEnd() .
                    "\n" . $widget->renderFooter();
            }
            echo $widget->renderDialogEnd();
        }
    }

    /**
     * Initializes and begins rendering the dialog part of the modal
     * including an ActiveForm.
     */
    public static function beginFormDialog($config = []): ActiveForm
    {
        $formConfig = $config['form'] ?? [];
        unset($config['form']);

        self::beginDialog($config, false);
        $form = ActiveForm::begin($formConfig);
        echo
            self::$stackForDialog->renderHeader() . "\n" .
            self::$stackForDialog->renderBodyBegin() . "\n";

        return $form;
    }

    public static function endFormDialog(): void
    {
        echo
            "\n" . self::$stackForDialog->renderBodyEnd() .
            "\n" . self::$stackForDialog->renderFooter();
        ActiveForm::end();
        self::endDialog(false);
    }
}

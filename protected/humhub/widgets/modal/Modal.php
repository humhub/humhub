<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\modal;

use yii\bootstrap5\Html;

/**
 * @inerhitdoc
 *
 * Provides an extension of the yii\bootstrap5\Modal class with additional features.
 *
 * @since 1.18
 * @see https://getbootstrap.com/docs/5.3/components/modal/
 */
class Modal extends \yii\bootstrap5\Modal
{
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
     * If true, prevents echo of the modal beginning tags.
     */
    public bool $bypassParentInit = false;

    /**
     * @var self the Modal that are currently being rendered (not ended). This property
     * is maintained by [[beginDialog()]] and [[endDialog()]] methods.
     * @internal
     */
    public static self $stackForDialog;

    protected function initOptions()
    {
        $this->title = $this->title ?: $this->header;

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

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        if ($this->bypassParentInit) {
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
        return
            Html::beginTag('div', $this->dialogOptions) . "\n" .
            Html::beginTag('div', ['class' => 'modal-content']) . "\n" .
            $this->renderHeader() . "\n" .
            $this->renderBodyBegin() . "\n";
    }

    public function renderDialogEnd(): string
    {
        return
            "\n" . $this->renderBodyEnd() .
            "\n" . $this->renderFooter() .
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
     *     'id' => 'modal-custom-id',
     *     'size' => Modal::SIZE_LARGE,
     *   ]) ?>
     *     <div class="modal-body">
     *       Content
     *     </div>
     *     <div class="modal-footer">
     *       <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
     *     </div>
     *   <?php Modal::endDialog()?>
     *   ```
     */
    public static function beginDialog($config = []): void
    {
        $config['bypassParentInit'] = true;
        $widget = new static($config);
        self::$stackForDialog = $widget;
        echo $widget->renderDialogBegin();
    }

    /**
     * Ends rendering the dialog part of the modal.
     */
    public static function endDialog(): void
    {
        $widget = self::$stackForDialog;
        if ($widget) {
            echo $widget->renderDialogEnd();
        }
    }
}

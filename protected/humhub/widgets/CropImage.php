<?php

namespace humhub\widgets;

use humhub\assets\CropImageAsset;
use humhub\components\Widget;
use humhub\helpers\Html;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * Lets the user choose a section of an image (`ui.cropImage`, built on Cropper.js)
 *
 * The widget renders the hint for the keyboard; the image is an `<img>` elsewhere, found by `$selector`.
 */
class CropImage extends Widget
{
    /**
     * @var string the selector of the `<img>` to crop
     */
    public $selector;

    /**
     * @var array the options of `ui.cropImage`, with the names of the Jcrop plugin used up to 1.19:
     * `aspectRatio`, `minSize`, `setSelect`, `bgColor`, `bgOpacity`, `boxWidth`, `onChange`, `keySupport`
     */
    public $pluginOptions = [];

    /**
     * @var int Min height of the image to crop
     */
    public int $minHeight = 250;

    /**
     * @var int Vertical space to subtract from window height
     * The space between the top of the window and the top of the image + the space between the bottom of the window and the bottom of the image
     */
    public int $verticalSpaceAround = 260;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->selector)) {
            throw new InvalidConfigException('The "selector" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();

        if (!$this->hasKeySupport()) {
            return '';
        }

        return Html::tag('p', Yii::t('base', 'Drag the frame to choose the section. With the keyboard, the arrow keys move it and + and - change its size.'), [
            'id' => $this->getHintId(),
            'class' => 'form-text',
        ]);
    }

    public function registerClientScript(): void
    {
        CropImageAsset::register($this->getView());

        $options = Json::htmlEncode(array_merge([
            'label' => Yii::t('base', 'Image section'),
            'describedBy' => $this->getHintId(),
        ], $this->pluginOptions));

        // The height of the window limits the height of the image
        $this->getView()->registerJs('humhub.require("ui.cropImage").create(' . Json::htmlEncode($this->selector) . ', '
            . '$.extend(' . $options . ', {boxHeight: Math.max($(window).height() - ' . $this->verticalSpaceAround . ', ' . $this->minHeight . ')}));');
    }

    private function hasKeySupport(): bool
    {
        return ($this->pluginOptions['keySupport'] ?? true) !== false;
    }

    private function getHintId(): string
    {
        return $this->getId() . '-hint';
    }
}

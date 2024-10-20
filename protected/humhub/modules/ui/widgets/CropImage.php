<?php

namespace humhub\modules\ui\widgets;

use raoul2000\jcrop\JCropAsset;
use raoul2000\jcrop\JCropWidget;
use yii\helpers\Json;

class CropImage extends JCropWidget
{
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
    public function registerClientScript(): void
    {
        $options = empty($this->pluginOptions) ? '' : Json::encode($this->pluginOptions);

        // Add window size detection to limit image height
        $js = '$(function () {let options = ' . $options . '; options.boxHeight = Math.max($(window).height() - ' . $this->verticalSpaceAround . ', ' . $this->minHeight . '); jQuery("' . $this->selector . '").Jcrop(options);});';

        $view = $this->getView();
        JCropAsset::register($view);
        $view->registerJs($js);
    }
}

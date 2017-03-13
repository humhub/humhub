<?php

namespace humhub\modules\file\widgets;

use yii\helpers\Html;

/**
 * UploadButtonWidget renders an upload button with integrated file input.
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadProgress extends \humhub\widgets\JsWidget
{
    
    public $jsWidget = "ui.progress.Progress";
    
    public $visible = false;
    
    public function getAttributes()
    {
        return [
            'style' => 'margin:10px 0px'
        ];
    }

}
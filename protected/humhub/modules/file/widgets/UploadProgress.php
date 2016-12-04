<?php

namespace humhub\modules\file\widgets;

use Yii;
use yii\helpers\Html;

/**
 * UploadButtonWidget renders an upload button with integrated file input.
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadProgress extends UploadInput
{
    
    public $jsWidget = "ui.progress.Progress";
    
    public $id;
    
    public $options = [];
    
    public $visible = false;
    
    /**
     * Draws the Upload Button output.
     */
    public function run()
    {   
        $defaultOptoins = [
            'id' => $this->id,
            'style' => 'margin:10px 0px;',
            'data' => [
                'ui-widget' => $this->jsWidget
            ]
        ];
        
        $options = \yii\helpers\ArrayHelper::merge($defaultOptoins, $this->options);
                
        if(!$this->visible) {
            $options['style'] .= 'display:none';
        }
        
        return Html::beginTag('div', $options).Html::endTag('div');
    }

}

?>
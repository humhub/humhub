<?php

namespace humhub\modules\file\widgets;

use Yii;

/**
 * UploadButtonWidget renders an upload button with integrated file input.
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadButton extends UploadInput
{
    public $buttonOptions = [];
    
    /**
     * Draws the Upload Button output.
     */
    public function run()
    {   
        $defaultButtonOptions = [
            'class' => 'btn btn-default fileinput-button tt',
            'title' => Yii::t('FileModule.widgets_views_fileUploadButton', 'Upload files'),
            'data' => [
                'placement' => "bottom",
                'action-click' => "file.upload", 
                'action-target' => '#'.$this->id
            ]
        ];
        
        $options = \yii\helpers\ArrayHelper::merge($defaultButtonOptions, $this->buttonOptions);
        
        return $this->render('uploadButton', [
                    'input' => parent::run(),
                    'options' => $options
        ]);
    }

}

?>
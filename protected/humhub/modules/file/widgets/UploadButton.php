<?php

namespace humhub\modules\file\widgets;

use Yii;

/**
 * FileUploadButtonWidget diplays a simple upload button with integrated file input.
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
        $defaultOptions = [
            'class' => 'btn btn-default fileinput-button tt',
            'title' => Yii::t('FileModule.widgets_views_fileUploadButton', 'Upload files'),
            'data' => [
                'placement' => "bottom",
                'action-click' => "file.upload", 
                'action-target' => '#'.$this->id
            ]
        ];
        
        $options = \yii\helpers\ArrayHelper::merge($defaultOptions, $this->buttonOptions);
        
        return $this->render('uploadButton', [
                    'input' => parent::run(),
                    'options' => $options
        ]);
    }

}

?>
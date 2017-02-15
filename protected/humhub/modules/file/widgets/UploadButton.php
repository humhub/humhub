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
    /**
     * Additional button html options.
     * @var array 
     */
    public $buttonOptions = [];
    
    /**
     * Show button tooltip on mousover.
     * @var boolean 
     */
    public $tooltip = true;
    
    /**
     * Tooltip position.
     * @var string 
     */
    public $tooltipPosition = 'bottom';
    
    /**
     * Draws the Upload Button output.
     */
    public function run()
    {   
        $defaultButtonOptions = [
            'class' => ($this->tooltip) ? 'btn btn-default fileinput-button tt' : 'btn btn-default fileinput-button',
            'title' => Yii::t('FileModule.widgets_views_fileUploadButton', 'Upload files'),
            'data' => [
                'placement' => $this->tooltipPosition,
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
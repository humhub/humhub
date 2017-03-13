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
     * Defines the button color class like btn-default, btn-primary
     * @var type 
     */
    public $cssButtonClass = 'btn-default';
    
    /**
     * Either defines a label string or true to use the default label.
     * If set to false, no button label is printed.
     * @var type 
     */
    public $label = false;
     
    /**
     * Draws the Upload Button output.
     */
    public function run()
    {   
        if($this->label === true) {
            $this->label = '&nbsp;'.Yii::t('base', 'Upload');
        } else if($this->label === false) {
            $this->label = '';
        } else {
            $this->label = '&nbsp;'.$this->label;
        }
        
        $defaultButtonOptions = [
            'class' => ($this->tooltip) ? 'btn '.$this->cssButtonClass.' fileinput-button tt' : 'btn '.$this->cssButtonClass.'  fileinput-button',
            'title' => ($this->tooltip === true) ? Yii::t('FileModule.widgets_views_fileUploadButton', 'Upload files') : $this->tooltip,
            'data' => [
                'placement' => $this->tooltipPosition,
                'action-click' => "file.upload", 
                'action-target' => '#'.$this->getId(true)
            ]
        ];
        
        $options = \yii\helpers\ArrayHelper::merge($defaultButtonOptions, $this->buttonOptions);
        
        return $this->render('uploadButton', [
                    'input' => parent::run(),
                    'options' => $options,
                    'label' => $this->label
        ]);
    }
}
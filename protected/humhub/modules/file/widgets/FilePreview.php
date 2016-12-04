<?php

namespace humhub\modules\file\widgets;

/**
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class FilePreview extends \yii\base\Widget
{   
    public $id;
    
    public $options = [];
    
    public $jsWidget = "file.Preview";
    
    public $items;
    
    public $model;
    
    public $hideImageFileInfo = false;
    
    public $edit = false;
    
    /**
     * Draws the Upload Button output.
     */
    public function run()
    {   
        if(!$this->id) {
            $this->id = $this->getId(true);
        }
        
        $defaultOptions = [
            'id' => $this->id,
            'data' => [
                'ui-widget' => $this->jsWidget,
                'file-edit' => $this->edit,
                'file-hide-image-files' => $this->hideImageFileInfo
            ],
            'style' => 'display:none;'
        ];
        
        // Initialize preview if data is given.
        $fileData = $this->getFileData();
        if(!empty($fileData)) {
            $defaultOptions['data']['ui-init'] = $fileData;
        }
        
        return \yii\helpers\Html::tag('div', '', \yii\helpers\ArrayHelper::merge($defaultOptions, $this->options));
    }
    
    protected function getFileData() {
        $files = $this->getFiles();
        $result = [];
        
        foreach ($files as $file) {
            $result[] = \humhub\modules\file\actions\UploadAction::getFileResponse($file);
        }
        
        return $result;
    }
    
    protected function getFiles() {
        if(!$this->items && !$this->model) {
            return [];
        }
        return ($this->items) ? $this->items : $this->model->fileManager->findAll();
    }
}
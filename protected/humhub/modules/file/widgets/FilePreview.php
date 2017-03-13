<?php

namespace humhub\modules\file\widgets;

use humhub\modules\file\libs\FileHelper;

/**
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class FilePreview extends \humhub\widgets\JsWidget
{

    public $jsWidget = "file.Preview";
    public $items;
    public $model;
    public $hideImageFileInfo = false;
    public $edit = false;
    public $visible = false;
    
    public $preventPopover = false;
    public $popoverPosition = 'right';

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        if (!$this->id) {
            $this->id = $this->getId(true);
        }

        // Initialize preview if data is given.
        $this->init = $this->getFileData();

        return \yii\helpers\Html::tag('div', '', $this->getOptions());
    }

    public function getData()
    {
        return [
            'prevent-popover' => $this->preventPopover,
            'popover-position' => $this->popoverPosition,
            'file-edit' => $this->edit,
            'hide-image-file-info' => $this->hideImageFileInfo
        ];
    }

    protected function getFileData()
    {
        $files = $this->getFiles();
        
        $result = [];

        foreach ($files as $file) {
            if($file) {
                $result[] = FileHelper::getFileInfos($file);
            }
        }

        return $result;
    }

    protected function getFiles()
    {
        if (!$this->items && !$this->model) {
            return [];
        }
        return ($this->items) ? $this->items : $this->model->fileManager->findAll();
    }

}

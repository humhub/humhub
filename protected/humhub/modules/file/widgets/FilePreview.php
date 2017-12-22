<?php

namespace humhub\modules\file\widgets;

use humhub\modules\file\libs\FileHelper;
use humhub\widgets\JsWidget;
use yii\helpers\Html;

/**
 * 
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class FilePreview extends JsWidget
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
     * @var bool defines if only files with show_in_stream falg should be viewed in case $model is used to load the files
     * @since 1.2.2
     */
    public $showInStream;

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

        return Html::tag('div', '', $this->getOptions());
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

        if($this->items) {
            return $this->items;
        }

        if($this->showInStream === null) {
            return $this->model->fileManager->findAll();
        } else {
            return $this->model->fileManager->findStreamFiles($this->showInStream);
        }
    }
}

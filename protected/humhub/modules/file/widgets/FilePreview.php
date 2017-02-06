<?php

namespace humhub\modules\file\widgets;

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
            'file-edit' => $this->edit,
            'file-hide-image-files' => $this->hideImageFileInfo
        ];
    }

    protected function getFileData()
    {
        $files = $this->getFiles();
        $result = [];

        foreach ($files as $file) {
            $result[] = \humhub\modules\file\actions\UploadAction::getFileResponse($file);
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

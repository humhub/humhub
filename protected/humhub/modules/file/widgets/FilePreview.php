<?php

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;
use humhub\modules\content\controllers\SearchController;
use humhub\modules\content\helpers\SearchHelper;
use humhub\modules\file\converter\TextConverter;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\widgets\JsWidget;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class FilePreview extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = "file.Preview";

    /**
     * @var File[]|string[] file items to display if no $model is given or $model is a new record.
     */
    public $items;

    /**
     * @var Model model object used to fetch default items if [[items]] is not set
     */
    public $model;

    /**
     * @var string model attribute used in combination with [[model]]
     */
    public $attribute;

    /**
     * @var bool weather or not to hide media files
     */
    public $excludeMediaFilesPreview = false;

    /**
     * @var bool weather or not the file previe is editable
     */
    public $edit = false;

    /**
     * @inheritdoc
     */
    public $visible = false;

    /**
     * @var bool weather or not to prevent popover previews for images
     */
    public $preventPopover = false;

    /**
     * @var string popover position
     */
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
            'exclude-media-files-preview' => (int)$this->excludeMediaFilesPreview,
        ];
    }

    public function getAttributes()
    {
        return [
            'class' => 'well post-file-list',
        ];
    }

    protected function getFileData()
    {
        $files = $this->getFiles();

        $result = [];

        foreach ($files as $file) {
            if ($file) {
                if (is_string($file)) {
                    $file = File::findOne(['guid' => $file]);
                }
                $result[] = ArrayHelper::merge(FileHelper::getFileInfos($file), ['highlight' => $this->isHighlighed($file)]);
            }
        }

        return $result;
    }

    protected function getFiles()
    {
        if (!$this->items && !$this->model) {
            return [];
        }

        if ($this->items) {
            return $this->items;
        }

        if (!($this->model instanceof ActiveRecord) && $this->attribute) {
            return Html::getAttributeValue($this->model, $this->attribute);
        }

        if (!($this->model instanceof ActiveRecord)) {
            return [];
        }

        if ($this->showInStream === null) {
            return $this->model->fileManager->findAll();
        } else {
            return $this->model->fileManager->findStreamFiles($this->showInStream);
        }
    }

    /**
     * Checks whether the file should be highlighed in the results or not.
     *
     * @param File $file
     * @return bool is highlighed
     */
    protected function isHighlighed(File $file)
    {
        if (Yii::$app->controller instanceof SearchController) {
            /** @var SearchController $searchController */
            $searchController = Yii::$app->controller;

            if (!empty($searchController->searchRequest->keyword)) {
                $converter = new TextConverter();
                if (
                    $converter->applyFile($file)
                    && SearchHelper::matchQuery($searchController->searchRequest->keyword, $converter->getContentAsText())
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}

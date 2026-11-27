<?php

namespace humhub\modules\file\widgets;

use humhub\assets\CoreVueAsset;
use humhub\components\ActiveRecord;
use humhub\helpers\Html;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\serializers\FileSerializer;
use humhub\widgets\JsWidget;
use humhub\widgets\VueComponent;
use yii\base\Model;

/**
 * Lists files as file rows (docs/ui/file-upload.md): the files of a form, with remove (`edit`), or a list of files
 * to download.
 *
 * The container is the root of the `file.Preview` widget (`humhub.file.js`); the rows are the core `<upload-list>`
 * Vue island inside it, which starts with the files given here. In a form the widget feeds the island with the
 * files being uploaded and removes files (see `vue/upload/uploadBridge.js`).
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
     * @var bool whether or not to hide media files; only applies without [[edit]], a form shows every file
     */
    public $excludeMediaFilesPreview = false;

    /**
     * @var bool whether or not the files can be removed (the files of a form)
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

        $items = $this->getItems();

        // The container shows while it has rows; in a form the widget shows and hides it
        $this->visible = $items !== [];

        // In a form the widget answers the island's remove requests, also without an upload button
        if ($this->edit) {
            $this->init = true;
        }

        $island = VueComponent::widget([
            'name' => 'UploadList',
            'assetBundle' => CoreVueAsset::class,
            'props' => [
                'items' => $items,
                'action' => $this->edit ? 'remove' : 'download',
                // An empty placement turns the image preview on hover off
                'popoverPlacement' => $this->preventPopover ? '' : $this->popoverPosition,
            ],
            'options' => ['data-upload-bridge' => true],
        ]);

        return Html::tag('div', $island, $this->getOptions());
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
            'class' => 'post-file-list',
        ];
    }

    /**
     * The rows the island starts with: the files in the API's shape ({@see FileSerializer::file()}), keyed by guid.
     *
     * @return array[]
     */
    protected function getItems(): array
    {
        $items = [];

        foreach ($this->getFiles() as $file) {
            if (is_string($file)) {
                $file = File::findOne(['guid' => $file]);
            }

            if (!$file instanceof File) {
                continue;
            }

            $data = $this->serializeFile($file);
            if (!$this->edit && $this->excludeMediaFilesPreview
                && in_array($data['mimeIcon'], ['mime-image', 'mime-video', 'mime-audio'], true)) {
                continue;
            }

            $items[] = ['key' => $file->guid, 'file' => $data];
        }

        return $items;
    }

    /**
     * The API's shape, plus the hints of a list to download (see {@see ShowFiles}): the view of the file and the
     * search highlight. A form's row links the file itself.
     */
    private function serializeFile(File $file): array
    {
        $data = FileSerializer::file($file);

        if (!$this->edit) {
            $viewUrl = FileHelper::getViewUrl($file);
            if ($viewUrl !== null) {
                $data['viewUrl'] = $viewUrl;
            }

            if (FileHelper::isSearchHighlighted($file)) {
                $data['highlight'] = true;
            }
        }

        return $data;
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

}

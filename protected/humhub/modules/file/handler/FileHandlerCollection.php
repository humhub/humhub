<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use Yii;

/**
 * FileHandlerCollection
 *
 * @since 1.2
 * @author Luke
 */
class FileHandlerCollection extends \yii\base\Component
{

    /**
     * @event the init event - use to register file handlers
     */
    const EVENT_INIT = 'init';

    /**
     * Collection Types
     */
    const TYPE_VIEW = 'view';
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';
    const TYPE_CREATE = 'create';
    const TYPE_EDIT = 'edit';

    /**
     * @var string current collection type
     */
    public $type;

    /**
     * @var File|null
     */
    public $file = null;

    /**
     * @var array
     */
    public $handlers = [];

    /**
     * @var string[] allowed text extensions
     */
    public $textExtensions = ['txt', 'log', 'xml'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->trigger(self::EVENT_INIT);

        // Register Core Handler
        if ($this->type === self::TYPE_EXPORT) {
            $this->register(Yii::createObject(['class' => DownloadFileHandler::class]));
        }

        if ($this->isViewableFile()) {
            $this->register(Yii::createObject(['class' => ViewFileHandler::class]));
        }

        if ($this->isEditableFile()) {
            $this->register(Yii::createObject(['class' => EditFileHandler::class]));
        }

        $this->sortHandler();
    }

    /**
     * @param \humhub\modules\file\components\BaseFileHandler $handler
     */
    public function register(BaseFileHandler $handler)
    {
        $handler->file = $this->file;
        $this->handlers[] = $handler;
    }

    /**
     * Returns registered handlers by type
     * 
     * @param string|array $type or multiple type array
     * @param \humhub\modules\file\models\File $file the file (optional)
     * @return BaseFileHandler[] the registered handlers
     */
    public static function getByType($types, $file = null)
    {
        $handlers = [];

        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            $handlers = array_merge($handlers, Yii::createObject([
                        'class' => static::class,
                        'file' => $file,
                        'type' => $type
                    ])->handlers);
        }
        return $handlers;
    }

    /**
     * Sorts the registered handlers
     */
    protected function sortHandler()
    {
        usort($this->handlers, function(BaseFileHandler $a, BaseFileHandler $b) {
            return strcmp($a->position, $b->position);
        });
    }

    protected function isTextFile(): bool
    {
        return $this->file instanceof File &&
            in_array(FileHelper::getExtension($this->file->file_name), $this->textExtensions);
    }

    protected function isViewableFile(): bool
    {
        return $this->type === self::TYPE_VIEW &&
            $this->isTextFile() &&
            $this->file->canRead() &&
            is_readable($this->file->getStore()->get());
    }

    protected function isEditableFile(): bool
    {
        return $this->type === self::TYPE_VIEW &&
            $this->isTextFile() &&
            $this->file->canEdit() &&
            is_writable($this->file->getStore()->get());
    }

}

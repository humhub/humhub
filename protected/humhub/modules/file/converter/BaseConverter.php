<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use humhub\modules\file\Module;
use Yii;
use humhub\modules\file\models\File;
use yii\base\BaseObject;

/**
 * BaseConverter
 *
 * @since 1.2
 * @author Luke
 */
abstract class BaseConverter extends BaseObject
{

    /**
     * @var File the file record
     */
    public $file;

    /**
     * All options used for the converted file variant.
     * These values also produce the unique ID of the cached returned file.
     *
     * @var array the converter options
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        if (!empty($module->converterOptions[get_class($this)])) {
            Yii::configure($this, $module->converterOptions[get_class($this)]);
        }
    }

    /**
     * Convert file
     */
    abstract protected function convert($fileName);

    /**
     * Returns if the given file can be converted
     */
    abstract protected function canConvert(File $file);

    /**
     * Returns the filename of the converted file.
     *
     * @return string the filename of converted file
     */
    public function getFilename()
    {
        $this->convert($this->getId());
        return $this->getId();
    }

    /**
     * Returns the ID of the converted file variant.
     * The default implementation creates a unique value from the `options` array value.
     *
     * @return string the id
     * @since 1.7
     */
    public function getId()
    {
        return 'v' . sprintf('%x', crc32(get_class($this) . http_build_query($this->options)));
    }

    /**
     * Returns the url to the converted file
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->file == null ? '' : $this->file->getUrl($this->getFileName());
    }

    /**
     * Sets file for the converter
     *
     * @param \humhub\modules\file\models\File $file
     * @return boolean returns false if file cannot be converted
     */
    public function applyFile(File $file)
    {
        $this->file = $file;
        if ($this->canConvert($file)) {
            return true;
        }

        return false;
    }

}

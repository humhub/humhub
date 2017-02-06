<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use humhub\modules\file\models\File;

/**
 * BaseConverter
 *
 * @since 1.2
 * @author Luke
 */
abstract class BaseConverter extends \yii\base\Object
{

    /**
     * @var File the file record
     */
    public $file;

    /**
     * All options used for the converted file variant
     * 
     * @var array
     */
    public $options = [];

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
     * The filename is a hash of used options and converter class.
     * 
     * @return string the filename of converted file
     */
    public function getFilename()
    {
        $fileName = 'v' . sprintf('%x', crc32($this->className() . http_build_query($this->options)));
        $this->convert($fileName);
        return $fileName;
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
        if ($this->canConvert($file)) {
            $this->file = $file;
            return true;
        }

        return false;
    }

}

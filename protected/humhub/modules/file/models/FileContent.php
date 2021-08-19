<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

/**
 * FileContent model is used to set a file by string
 * 
 * @author Luke
 * @inheritdoc
 * @since 1.2
 */
class FileContent extends File
{

    /**
     * @var string file content 
     */
    public $newFileContent = null;

    /**
     * @var bool Require content
     */
    public $requireContent = true;

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->newFileContent = file_get_contents($this->getStore()->get());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if ($this->newFileContent && $this->size === null) {
            $this->setFileSize();
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['newFileContent'], $this->requireContent ? 'required' : 'safe'],
        ];

        return array_merge(parent::rules(), $rules);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->store->setContent($this->newFileContent);
    }

    /**
     * Sets the file size by newFileContent
     */
    protected function setFileSize()
    {
        if (function_exists('mb_strlen')) {
            $this->size = mb_strlen($this->newFileContent, '8bit');
        } else {
            $this->size = strlen($this->newFileContent);
        }
    }

}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\file\components\FileManager;

/**
 * Description of ActiveRecord
 *
 * @property FileManager $fileManager
 * @author luke
 */
class ActiveRecord extends \yii\db\ActiveRecord implements \Serializable
{

    /**
     * @var \humhub\modules\file\components\FileManager
     */
    private $_fileManager;

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->hasAttribute('created_at') && $this->created_at == "") {
                $this->created_at = new \yii\db\Expression('NOW()');
            }

            if (isset(Yii::$app->user) && $this->hasAttribute('created_by') && $this->created_by == "") {
                $this->created_by = Yii::$app->user->id;
            }
        }

        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = new \yii\db\Expression('NOW()');
        }
        if (isset(Yii::$app->user) && $this->hasAttribute('updated_by')) {
            $this->updated_by = Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    /**
     * Returns a unique id for this record/model
     *
     * @return String Unique Id of this record
     */
    public function getUniqueId()
    {
        return str_replace('\\', '', get_class($this)) . "_" . $this->primaryKey;
    }

    /**
     * Relation to User defined in created_by attribute
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), [
            'id' => 'created_by'
        ]);
    }

    /**
     * Relation to User defined in updated_by attribute
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), [
            'id' => 'updated_by'
        ]);
    }

    /**
     * Returns the file manager for this record
     *
     * @return FileManager the file manager instance
     */
    public function getFileManager()
    {
        if ($this->_fileManager === null) {
            $this->_fileManager = new FileManager([
                'record' => $this
            ]);
        }

        return $this->_fileManager;
    }

    /**
     * Returns the errors as string for all attribute or a single attribute.
     *
     * @since 1.2
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return string the error message
     */
    public function getErrorMessage($attribute = null)
    {
        $message = '';
        foreach ($this->getErrors($attribute) as $attribute => $errors) {
            $message .= $attribute . ': ' . implode(', ', $errors) . ', ';
        }

        return $message;
    }

    /**
     * Serializes attributes and oldAttributes of this record.
     *
     * Note: Subclasses have to include $this->getAttributes() and $this->getOldAttributes()
     * in the serialized array.
     *
     * @link http://php.net/manual/en/function.serialize.php
     * @since 1.2
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'attributes' => $this->getAttributes(),
            'oldAttributes' => $this->getOldAttributes()
        ]);
    }

    /**
     * Unserializes the given string, calls the init() function and sets the attributes and oldAttributes.
     *
     * Note: Subclasses have to call $this->init() if overwriting this function.
     *
     * @link http://php.net/manual/en/function.unserialize.php
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->init();
        $unserializedArr = unserialize($serialized);
        $this->setAttributes($unserializedArr['attributes'],false);
        $this->setOldAttributes($unserializedArr['oldAttributes'],false);
    }

}

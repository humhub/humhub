<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
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
class ActiveRecord extends \yii\db\ActiveRecord
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
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Relation to User defined in updated_by attribute
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Returns the file manager for this record
     * 
     * @return FileManager the file manager instance
     */
    public function getFileManager()
    {
        if ($this->_fileManager === null) {
            $this->_fileManager = new FileManager(['record' => $this]);
        }

        return $this->_fileManager;
    }

}

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
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\validators\Validator;

/**
 * Description of ActiveRecord
 *
 * @property FileManager $fileManager
 * @property User $createdBy
 * @property User $updatedBy
 * @author luke
 */
class ActiveRecord extends \yii\db\ActiveRecord
{

    /**
     * @var \humhub\modules\file\components\FileManager
     */
    private $_fileManager;

    /**
     * @var bool enable file history for attached files
     * @since 1.10
     */
    public $fileManagerEnableHistory = false;

    /**
     * @event Event is used to append rules what defined in [[rules()]].
     */
    const EVENT_APPEND_RULES = 'appendRules';

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->hasAttribute('created_at') && $this->created_at == "") {
                $this->created_at = date('Y-m-d G:i:s');
            }

            if (isset(Yii::$app->user) && $this->hasAttribute('created_by') && $this->created_by == "") {
                $this->created_by = Yii::$app->user->id;
            }
        }

        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = date('Y-m-d G:i:s');
        }
        if (isset(Yii::$app->user) && $this->hasAttribute('updated_by')) {
            $this->updated_by = Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->hasAttribute('created_at') && $this->created_at instanceof Expression) {
            $this->created_at = date('Y-m-d G:i:s');
        }

        if($this->hasAttribute('updated_at') && $this->updated_at instanceof Expression) {
            $this->updated_at = date('Y-m-d G:i:s');
        }

        parent::afterSave($insert, $changedAttributes);
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
     * @return User|null
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, [
            'id' => 'created_by'
        ]);
    }

    /**
     * Relation to User defined in updated_by attribute
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, [
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
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'attributes' => $this->getAttributes(),
            'oldAttributes' => $this->getOldAttributes()
        ];
    }

    /**
     * Unserializes the given string, calls the init() function and sets the attributes and oldAttributes.
     *
     * Note: Subclasses have to call $this->init() if overwriting this function.
     *
     * @link http://php.net/manual/en/function.unserialize.php
     * @param array $unserializedArr
     */
    public function __unserialize($unserializedArr)
    {
        $this->init();
        $this->setAttributes($unserializedArr['attributes'],false);
        $this->setOldAttributes($unserializedArr['oldAttributes']);
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLabel($attribute)
    {
        return $attribute === null ? '' : parent::getAttributeLabel($attribute);
    }

    /**
     * @inheritdoc
     */
    public function createValidators()
    {
        $validators = parent::createValidators();

        $event = new Event();
        $this->trigger(self::EVENT_APPEND_RULES, $event);

        if (is_array($event->result)) {
            foreach ($event->result as $rule) {
                if ($rule instanceof Validator) {
                    $validators->append($rule);
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                    $validator = Validator::createValidator($rule[1], $this, (array)$rule[0], array_slice($rule, 2));
                    $validators->append($validator);
                } else {
                    throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
                }
            }
        }

        return $validators;
    }
}

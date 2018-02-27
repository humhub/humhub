<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db;

/**
 * @property string $errorMessage
 * @property string $uniqueId
 * @since 1.3
 */
class HActiveRecord extends \yii\db\ActiveRecord implements \Serializable
{
    /**
     * Returns a unique id for this record/model
     *
     * @return string Unique Id of this record
     */
    public function getUniqueId()
    {
        if (is_array($this->primaryKey)) {
            $id = get_class($this);
            foreach ($this->primaryKey as $k => $v) {
                $id .= ' ' . $k;
            }
            return str_replace('\\', '', $id);
        }
        return str_replace('\\', '', get_class($this)) . "_" . $this->primaryKey;
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
            'oldAttributes' => $this->getOldAttributes(),
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
        $this->setAttributes($unserializedArr['attributes'], false);
        $this->setOldAttributes($unserializedArr['oldAttributes']);
    }
}

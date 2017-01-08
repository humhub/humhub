<?php

namespace humhub\modules\content\tests\codeception\unit\models;

/**
 * Description of TestContent
 *
 * @author buddha
 */
class TestContent extends \humhub\modules\content\components\ContentActiveRecord
{

    public $message;
    public $_content;

    /**
     * @inheritdoc
     */
    public $wallEntryClass = 'humhub\modules\content\tests\codeception\unit\widgets\WallEntryTest';

    public function save($runValidation = true, $attributeNames = null)
    {
        // Just a mock...
        $this->afterSave(true, []);
    }

    public function getPrimaryKey($asArray = false)
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        /**
         * Ensure there is always a corresponding Content 
         */
        if ($name == 'content') {
            if (!$this->_content) {
                $this->_content = new \humhub\modules\content\models\Content();
                $this->_content->setPolymorphicRelation($this);
            }
            return $this->_content;
        }
        return parent::__get($name);
    }

}

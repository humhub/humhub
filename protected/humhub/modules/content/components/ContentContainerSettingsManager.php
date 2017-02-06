<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use humhub\libs\BaseSettingsManager;

/**
 * ContentContainerSettingManager
 *
 * @since 1.1
 * @author Luke
 */
class ContentContainerSettingsManager extends BaseSettingsManager
{

    /**
     * @inheritdoc
     */
    public $modelClass = 'humhub\modules\content\models\ContentContainerSetting';

    /**
     * @var ContentContainerActiveRecord the content container this settings manager belongs to
     */
    public $contentContainer;
    
    /**
     * Returns the setting value of this container for the given setting $name.
     * If there is not container specific setting, this function will search for a global setting or
     * return default or null if there is also no global setting.
     * 
     * @param type $name
     * @param type $default
     * @return boolean
     * @since 1.2
     */
    public function getInherit($name, $default = null) {
        $result = $this->get($name);
        return ($result !== null) ? $result
            : Yii::$app->getModule($this->moduleId)->settings->get($name, $default);
    }
    
    /**
     * Returns the setting value of this container for the given setting $name.
     * If there is not container specific setting, this function will search for a global setting or
     * return default or null if there is also no global setting.
     * 
     * @param type $name
     * @param type $default
     * @return boolean
     * @since 1.2
     */
    public function getSerializedInherit($name, $default = null) {
        $result = $this->getSerialized($name);
        return ($result !== null) ? $result
            : Yii::$app->getModule($this->moduleId)->settings->getSerialized($name, $default);
    }

    /**
     * @inheritdoc
     */
    protected function createRecord()
    {
        $record = parent::createRecord();
        $record->contentcontainer_id = $this->contentContainer->contentContainerRecord->id;
        return $record;
    }

    /**
     * @inheritdoc
     */
    protected function find()
    {
        return parent::find()->andWhere(['contentcontainer_id' => $this->contentContainer->contentContainerRecord->id]);
    }

    /**
     * @inheritdoc
     */
    protected function getCacheKey()
    {
        return parent::getCacheKey() . '-' . $this->contentContainer->id;
    }

}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\libs\BaseSettingsManager;
use Yii;

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
    public string $modelClass = 'humhub\modules\content\models\ContentContainerSetting';

    /**
     * @var ContentContainerActiveRecord the content container this settings manager belongs to
     */
    public $contentContainer;

    /**
     * Returns the setting value of this container for the given setting $name.
     * If there is not container specific setting, this function will search for a global setting or
     * return default or null if there is also no global setting.
     *
     * @param string $name
     * @param string $default
     * @return bool
     * @since 1.2
     */
    public function getInherit($name, $default = null)
    {
        $result = $this->get($name);
        return ($result !== null) ? $result
            : Yii::$app->getModule($this->moduleId)->settings->get($name, $default);
    }

    /**
     * Returns the setting value of this container for the given setting $name.
     * If there is not container specific setting, this function will search for a global setting or
     * return default or null if there is also no global setting.
     *
     * @param string $name
     * @param string $default
     * @return bool
     * @since 1.2
     */
    public function getSerializedInherit($name, $default = null)
    {
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
        $record->contentcontainer_id = $this->contentContainer->contentcontainer_id;
        return $record;
    }

    /**
     * @inheritdoc
     */
    protected function find()
    {
        return parent::find()->andWhere(['contentcontainer_id' => $this->contentContainer->contentcontainer_id]);
    }

    /**
     * @inheritdoc
     */
    protected function getCacheKey(): string
    {
        /** @var SettingActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        return $modelClass::getCacheKey($this->moduleId, $this->contentContainer->contentcontainer_id);
    }

}

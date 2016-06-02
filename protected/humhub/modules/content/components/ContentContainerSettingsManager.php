<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

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

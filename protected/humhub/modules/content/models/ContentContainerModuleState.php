<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\models\Setting;
use humhub\modules\content\components\ContentContainerModule;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contentcontainer_module".
 *
 * @property integer $contentcontainer_id
 * @property string $module_id
 * @property integer $module_state
 *
 * @property ContentContainer $contentContainer
 */
class ContentContainerModuleState extends ActiveRecord
{
    /** @var int */
    const STATE_DISABLED = 0;

    /** @var int */
    const STATE_ENABLED = 1;

    /** @var int */
    const STATE_FORCE_ENABLED = 2;

    /** @var int */
    const STATE_NOT_AVAILABLE = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_module';
    }

    /**
     * @param false $labels
     * @return array|int[]|string[]
     */
    public static function getStates($labels = false)
    {
        $states = [
            self::STATE_NOT_AVAILABLE => Yii::t('AdminModule.modules', 'Not available'),
            self::STATE_DISABLED => Yii::t('AdminModule.modules', 'Deactivated'),
            self::STATE_ENABLED => Yii::t('AdminModule.modules', 'Activated'),
            self::STATE_FORCE_ENABLED => Yii::t('AdminModule.modules', 'Always activated')
        ];

        return $labels ? $states : array_keys($states);
    }

    /**
     * @throws ReflectionException
     */
    public static function getExcludedContentClasses(string $containerClass): array
    {
        $reflectClass = new ReflectionClass($containerClass);

        // Find modules with "Not Available" option per User/Space
        $moduleIds = Setting::find()
            ->select('module_id')
            ->where(['name' => 'moduleManager.defaultState.' . $reflectClass->getShortName()])
            ->andWhere(['value' => ContentContainerModuleState::STATE_NOT_AVAILABLE])
            ->column();

        if (empty($moduleIds)) {
            return [];
        }

        $excludedContentClasses = [];
        foreach ($moduleIds as $moduleId) {
            $module = Yii::$app->getModule($moduleId);
            if ($module instanceof ContentContainerModule) {
                $excludedContentClasses = array_merge($excludedContentClasses, $module->getContentClasses());
            }
        }

        return $excludedContentClasses;
    }

    /**
     * @return ActiveQuery
     */
    public function getContentContainer()
    {
        return $this
            ->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }
}

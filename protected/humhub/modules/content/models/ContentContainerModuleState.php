<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contentcontainer_module".
 *
 * @property integer $contentcontainer_id
 * @property string $module_id
 * @property integer $module_state
 */
class ContentContainerModuleState extends ActiveRecord
{
    /** @var int */
    const STATE_DISABLED = 0;

    /** @var int */
    const STATE_ENABLED = 1;

    /** @var int */
    const STATE_FORCE_ENABLED = 2;

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
            self::STATE_DISABLED => Yii::t('AdminModule.modules', 'Deactivated'),
            self::STATE_ENABLED => Yii::t('AdminModule.modules', 'Activated'),
            self::STATE_FORCE_ENABLED => Yii::t('AdminModule.modules', 'Always activated')
        ];

        return $labels ? $states : array_keys($states);
    }
}

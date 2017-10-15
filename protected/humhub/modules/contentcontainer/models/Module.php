<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\contentcontainer\models;

use Yii;

/**
 * This is the model class for table "contentcontainer_module".
 *
 * @property integer $contentcontainer_id
 * @property string $module_id
 * @property integer $module_state
 *
 * @property Contentcontainer $contentcontainer
 */
class Module extends \yii\db\ActiveRecord
{

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_FORCE_ENABLED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_module';
    }

}

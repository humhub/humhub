<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "contentcontainer_permission".
 *
 * @property string $permission_id
 * @property int $contentcontainer_id
 * @property string $group_id
 * @property string $module_id
 * @property string $class
 * @property int $state
 */
class ContentContainerPermission extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['permission_id', 'contentcontainer_id', 'group_id', 'module_id'], 'required'],
            [['contentcontainer_id', 'state'], 'integer'],
            [['permission_id', 'group_id', 'module_id', 'class'], 'string', 'max' => 255],
        ];
    }

}

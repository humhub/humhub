<?php

namespace humhub\modules\user\models;

use humhub\components\PermissionActiveRecord;

/**
 * This is the model class for table "group_permission".
 *
 * @property integer $group_id
 */
class GroupPermission extends PermissionActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_permission';
    }
}

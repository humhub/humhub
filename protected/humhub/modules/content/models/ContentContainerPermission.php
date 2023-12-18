<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\PermissionActiveRecord;

/**
 * This is the model class for table "contentcontainer_permission".
 *
 * @property string $group_id
 * @property integer $contentcontainer_id
 */
class ContentContainerPermission extends PermissionActiveRecord
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
            [['permission_id', 'group_id', 'module_id', 'class'], 'string', 'max' => 255]
        ];
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

/**
 * This is the model class for table "contentcontainer_default_permission".
 *
 * @property string $permission_id
 * @property string $contentcontainer_class
 * @property string $group_id
 * @property string $module_id
 * @property string $class
 * @property integer $state
 */
class ContentContainerDefaultPermission extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_default_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['permission_id', 'contentcontainer_class', 'group_id', 'module_id'], 'required'],
            [['state'], 'integer'],
            [['permission_id'], 'string', 'max' => 150],
            [['contentcontainer_class', 'class'], 'string', 'max' => 255],
            [['group_id', 'module_id'], 'string', 'max' => 50],
        ];
    }

}

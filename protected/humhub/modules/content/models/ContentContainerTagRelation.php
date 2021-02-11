<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;

/**
 * Class ContentContainerTagRelation
 *
 * @property integer $contentcontainer_id
 * @property integer $tag_id
 *
 * @since 1.8
 */
class ContentContainerTagRelation extends ActiveRecord
{
    public static function tableName()
    {
        return 'contentcontainer_tag_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contentcontainer_id', 'tag_id'], 'required'],
            [['contentcontainer_id', 'tag_id'], 'integer'],
        ];
    }
}

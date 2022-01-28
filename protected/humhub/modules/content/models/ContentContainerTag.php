<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * Tags of content containers User|Space
 *
 * @property integer $id
 * @property string $name
 * @property string $contentcontainer_class
 *
 * @package humhub\modules\content\models
 * @since 1.9
 */
class ContentContainerTag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'contentcontainer_class'], 'required'],
            [['name'], 'string', 'max' => '100'],
            [['contentcontainer_class'], 'string', 'max' => '60'],
            [['name'], 'validateUniqueName'],
        ];
    }

    /**
     * Validate unique tag name
     *
     * @param string $attribute
     */
    public function validateUniqueName($attribute)
    {
        $query = static::find()
            ->where(['name' => $this->$attribute])
            ->andWhere(['contentcontainer_class' => $this->contentcontainer_class]);

        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        if ($query->count() > 0) {
            $this->addError('name', Yii::t('ContentModule.base', 'The given name is already in use.'));
        }
    }

    /**
     * Returns Tags related to the Content Container.
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @return ActiveQuery
     */
    public static function findByContainer($contentContainer)
    {
        return static::find()
            ->leftJoin('contentcontainer_tag_relation', 'contentcontainer_tag.id = contentcontainer_tag_relation.tag_id')
            ->where(['contentcontainer_tag_relation.contentcontainer_id' => $contentContainer->contentcontainer_id]);
    }
}

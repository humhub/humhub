<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Class ContentContainerTagRelation
 *
 * @property integer $contentcontainer_id
 * @property integer $tag_id
 *
 * @since 1.9
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

    /**
     * Get tag names of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @return string[]
     */
    public static function getNamesByContainer($contentContainer)
    {
        return ContentContainerTag::find()
            ->select('name')
            ->leftJoin('contentcontainer_tag_relation', 'id = tag_id')
            ->where(['contentcontainer_id' => $contentContainer->contentcontainer_id])
            ->andWhere(['contentcontainer_class' => $contentContainer->contentContainerRecord->class])
            ->column();
    }

    /**
     * Update tag relations of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @param string[]|null $newTags
     */
    public static function updateByContainer($contentContainer, $newTags = null)
    {
        self::deleteByContainer($contentContainer);

        if (empty($newTags)) {
            return;
        }

        $existingTags = ContentContainerTag::find()
            ->select(['id', 'name'])
            ->where(['IN', 'name', $newTags])
            ->andWhere(['contentcontainer_class' => $contentContainer->contentContainerRecord->class])
            ->all();

        $existingTagsArray = [];
        /* @var $existingTag ContentContainerTag */
        foreach ($existingTags as $existingTag) {
            $existingTagsArray[$existingTag->name] = $existingTag->id;
        }

        foreach ($newTags as $updatedTag) {
            $newTagRelation = new ContentContainerTagRelation();
            $newTagRelation->contentcontainer_id = $contentContainer->contentcontainer_id;
            if (isset($existingTagsArray[$updatedTag])) {
                $newTagRelation->tag_id = $existingTagsArray[$updatedTag];
            } else {
                $newTag = new ContentContainerTag();
                $newTag->name = $updatedTag;
                $newTag->contentcontainer_class = $contentContainer->contentContainerRecord->class;
                $newTag->save();
                $newTagRelation->tag_id = $newTag->id;
            }
            $newTagRelation->save();
        }

        $contentContainer->contentContainerRecord->updateAttributes([
            'tags_cached' => implode(', ', ContentContainerTagRelation::getNamesByContainer($contentContainer))
        ]);
    }


    /**
     * Delete tag relations of the Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     */
    public static function deleteByContainer($contentContainer)
    {
        $tagRelations = self::find()
            ->where(['contentcontainer_id' => $contentContainer->contentcontainer_id])
            ->all();

        foreach ($tagRelations as $tagRelation) {
            $tagRelation->delete();
        }

        $contentContainer->contentContainerRecord->updateAttributes([
            'tags_cached' => null
        ]);
    }
}

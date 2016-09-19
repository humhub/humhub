<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use humhub\modules\content\models\Content;

/**
 * ContentContainerStream is used to stream contentcontainers (space or users) content.
 * 
 * Used to stream contents of a specific a content container.
 *
 * @since 0.11
 * @author luke
 */
class ContentContainerStream extends Stream
{

    /**
     * @var \humhub\modules\content\components\ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Limit to this content container
        $this->activeQuery->andWhere(['content.contentcontainer_id' => $this->contentContainer->contentContainerRecord->id]);

        // Limit to public posts when no member
        if (!$this->contentContainer->canAccessPrivateContent($this->user)) {
            if (!Yii::$app->user->isGuest) {
                $this->activeQuery->andWhere("content.visibility=" . Content::VISIBILITY_PUBLIC . " OR content.created_by = :userId", [':userId' => $this->user->id]);
            } else {
                $this->activeQuery->andWhere("content.visibility=" . Content::VISIBILITY_PUBLIC);
            }
        }

        // Add all sticked contents to initial request
        if ($this->isInitialRequest()) {
            // Get number of sticked contents
            $stickedQuery = clone $this->activeQuery;
            $stickedQuery->andWhere(['content.sticked' => 1]);
            $stickedCount = $stickedQuery->count();

            // Increase query result limit to ensure there are also not sticked entries
            $this->activeQuery->limit += $stickedCount;

            // Modify order - sticked content first
            $oldOrder = $this->activeQuery->orderBy;
            $this->activeQuery->orderBy("");
            $this->activeQuery->addOrderBy('content.sticked DESC');
            $this->activeQuery->addOrderBy($oldOrder);
        } else {
            // No sticked content in further queries
            $this->activeQuery->andWhere("content.sticked = 0");
        }
    }

}

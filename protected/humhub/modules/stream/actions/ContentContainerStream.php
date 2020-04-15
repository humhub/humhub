<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use Yii;
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

        if ($this->contentContainer) {
            $this->handleContentContainer();
        }
    }

    /**
     * Limits the stream to a given ContentContainer and adds basic visibility handling.
     */
    protected function handleContentContainer()
    {
        // Limit to this content container
        $this->activeQuery->andWhere(['content.contentcontainer_id' => $this->contentContainer->contentcontainer_id]);

        // Limit to public posts when no member
        if (!$this->contentContainer->canAccessPrivateContent($this->user)) {
            if (!Yii::$app->user->isGuest) {
                $this->activeQuery->andWhere("content.visibility=" . Content::VISIBILITY_PUBLIC . " OR content.created_by = :userId", [':userId' => $this->user->id]);
            } else {
                $this->activeQuery->andWhere("content.visibility=" . Content::VISIBILITY_PUBLIC);
            }
        }

        $this->handlePinnedContent();
    }

    /**
     * Make sure pinned contents are returned first.
     */
    protected function handlePinnedContent()
    {
        // Add all pinned contents to initial request
        if ($this->isInitialRequest()) {
            // Get number of pinned contents
            $pinnedQuery = clone $this->activeQuery;
            $pinnedQuery->andWhere(['AND', ['content.pinned' => 1], ['content.contentcontainer_id' => $this->contentContainer->contentcontainer_id]]);
            $pinnedCount = $pinnedQuery->count();

            // Increase query result limit to ensure there are also not pinned entries
            $this->activeQuery->limit += $pinnedCount;

            // Modify order - pinned content first
            $oldOrder = $this->activeQuery->orderBy;
            $this->activeQuery->orderBy("");
            $this->activeQuery->addOrderBy('content.pinned DESC');


            // Only include pinned content of the current contentcontainer (profile stream)
            $this->activeQuery->andWhere([
                'OR',
                [
                    'AND',
                    ['content.pinned' => 1],
                    ['content.contentcontainer_id' => $this->contentContainer->contentcontainer_id],
                ],
                ['content.pinned' => 0],
            ]);

            $this->activeQuery->addOrderBy($oldOrder);
        } else {
            // No pinned content in further queries
            $this->activeQuery->andWhere("content.pinned = 0");
        }

    }

}

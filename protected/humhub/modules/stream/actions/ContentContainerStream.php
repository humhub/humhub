<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use Yii;
use humhub\modules\content\models\Content;
use yii\db\ArrayExpression;
use yii\db\Expression;

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
     * Handles ordering of pinned content entries.
     */
    protected function handlePinnedContent()
    {
        // Add all pinned contents to initial request
        if ($this->isInitialRequest()) {
            // Get number of pinned contents
            $pinnedQuery = clone $this->activeQuery;
            $pinnedQuery->andWhere(['AND', ['content.pinned' => 1], ['content.contentcontainer_id' => $this->contentContainer->contentcontainer_id]]);
            $pinnedContent = $pinnedQuery->select('content.id')->column();

            if(!empty($pinnedContent)) {
                // Increase query result limit to ensure all pinned entries are included in the first request
                $this->activeQuery->limit += count($pinnedContent);

                // Modify order - pinned content first
                $oldOrder = $this->activeQuery->orderBy;
                $this->activeQuery->orderBy("");
                $this->activeQuery->addOrderBy(new Expression('CASE WHEN `content`.`id` IN ('.implode(',', $pinnedContent).') THEN 1 else 0 END DESC'));
                $this->activeQuery->addOrderBy($oldOrder);
            }

        } else {
            // All pinned entries of this container were loaded within the initial request, so don't include them here!
            $this->activeQuery->andWhere(['OR', ['content.pinned' => 0], ['<>', 'content.contentcontainer_id', $this->contentContainer->contentcontainer_id]]);
        }
    }
}

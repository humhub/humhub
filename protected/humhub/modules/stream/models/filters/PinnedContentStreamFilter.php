<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use yii\db\Expression;

/**
 * This stream filter manages the stream order of container streams with pinned content support. Once added to a StreamQuery,
 * this filter will be active and can not be deactivated by request parameter.
 *
 * When active, pinned content entries of the given container will be prepended to the StreamQuery result.
 * This is done by modifying the query limit and query order.
 *
 * Since this filter modifies the query order, it should be added at the end of the filter list.
 *
 * @package humhub\modules\stream\models\filters
 * @since 1.6
 */
class PinnedContentStreamFilter extends StreamQueryFilter
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $container;

    /**
     * @var Content[]
     */
    public $pinnedContent = [];

    public function apply()
    {
        //$this->oldApply();
        //$this->newApply();
        $this->newestApply();
    }

    /**
     * @inheritDoc
     */
    public function newestApply()
    {
        // Currently we only support pinned entries on container streams
        if(!$this->container) {
            return;
        }

        // Add all pinned contents to initial request
        if ($this->streamQuery->isInitialQuery()) {
            // Get number of pinned contents
            $pinnedContentIds = $this->fetchPinnedContent();

            if(!empty($pinnedContentIds)) {
                // Increase query result limit to ensure all pinned entries are included in the first request
                $this->query->andWhere((['NOT IN', 'content.id', $pinnedContentIds]));
            }
        } else {
            // All pinned entries of this container were loaded within the initial request, so don't include them here!
            $this->query->andWhere(['OR', ['content.pinned' => 0], ['<>', 'content.contentcontainer_id', $this->container->contentcontainer_id]]);
        }
    }

    /**
     * @inheritDoc
     */
    public function newApply()
    {
        // Currently we only support pinned entries on container streams
        if(!$this->container) {
            return;
        }

        // Add all pinned contents to initial request
        if ($this->streamQuery->isInitialQuery()) {
            // Get number of pinned contents
            $pinnedQuery = clone $this->query;
            $pinnedQuery->andWhere(['AND', ['content.pinned' => 1], ['content.contentcontainer_id' => $this->container->contentcontainer_id]]);
            $pinnedQuery->limit(1000);
            $pinnedContentIds = $pinnedQuery->select('content.id')->column();

            if(!empty($pinnedContentIds)) {
                // Increase query result limit to ensure all pinned entries are included in the first request
                $this->streamQuery->limit($this->streamQuery->limit + count($pinnedContentIds)) ;

                // Modify order - pinned content first
                $oldOrder = $this->query->orderBy;
                $this->query->orderBy("");
                $this->query->addOrderBy(new Expression('CASE WHEN `content`.`id` IN ('.implode(',', $pinnedContentIds).') THEN 1 else 0 END DESC'));
                $this->query->addOrderBy($oldOrder);
            }
        } else {
            // All pinned entries of this container were loaded within the initial request, so don't include them here!
            $this->query->andWhere(['OR', ['content.pinned' => 0], ['<>', 'content.contentcontainer_id', $this->container->contentcontainer_id]]);
        }
    }

    private function fetchPinnedContent()
    {
        $pinnedQuery = clone $this->query;
        $pinnedQuery->andWhere(['AND', ['content.pinned' => 1], ['content.contentcontainer_id' => $this->container->contentcontainer_id]]);
        $pinnedQuery->limit(1000);
        $this->pinnedContent = $pinnedQuery->all();
        return array_map(function($content) {
            return $content->id;
        }, $this->pinnedContent);
    }

    public function oldApply()
    {
        // Currently we only support pinned entries on container streams
        if(!$this->container) {
            return;
        }

        // Add all pinned contents to initial request
        if ($this->streamQuery->isInitialQuery()) {
            // Get number of pinned contents
            $pinnedQuery = clone $this->query;
            $pinnedQuery->andWhere(['AND', ['content.pinned' => 1], ['content.contentcontainer_id' => $this->container->contentcontainer_id]]);
            $pinnedCount = $pinnedQuery->count();

            // Increase query result limit to ensure there are also not pinned entries
            $this->streamQuery->limit($this->streamQuery->limit + $pinnedCount) ;

            // Modify order - pinned content first
            $oldOrder = $this->query->orderBy;
            $this->query->orderBy("");
            $this->query->addOrderBy('content.pinned DESC');


            // Only include pinned content of the current contentcontainer (profile stream)
            $this->query->andWhere([
                'OR',
                [
                    'AND',
                    ['content.pinned' => 1],
                    ['content.contentcontainer_id' => $this->container->contentcontainer_id],
                ],
                ['content.pinned' => 0],
            ]);

            $this->query->addOrderBy($oldOrder);
        } else {
            // No pinned content in further queries
            $this->query->andWhere("content.pinned = 0");
        }
    }
}

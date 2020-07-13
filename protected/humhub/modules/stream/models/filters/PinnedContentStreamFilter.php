<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\content\components\ContentContainerActiveRecord;
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
     * @inheritDoc
     */
    public function apply()
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
            $pinnedContentIds = $pinnedQuery->select('content.id')->column();

            if(!empty($pinnedContentIds)) {
                // Increase query result limit to ensure all pinned entries are included in the first request
                $this->query->limit += count($pinnedContentIds);

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
}

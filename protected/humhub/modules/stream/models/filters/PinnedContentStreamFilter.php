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

/**
 * This stream filter manages the stream order of container streams with pinned content support.
 * This filter can not be deactivated by request parameter.
 *
 * This filter will fetch pinned content entries of the given [[container]] respecting all active stream filters
 * and exclude the pinned content entries from the stream query result.
 *
 * The pinned content entries can be accessed by [[pinnedContent]] property after this filter was applied.
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

    /**
     * @inheritDoc
     */
    public function apply()
    {
        // Currently we only support pinned entries on container streams
        if(!$this->container) {
            return;
        }

         if ($this->streamQuery->isInitialQuery()) {
             $pinnedContentIds = $this->fetchPinnedContent();

             // Exclude pinned content from result, we've already fetched and cached them
             if(!empty($pinnedContentIds)) {
                $this->query->andWhere((['NOT IN', 'content.id', $pinnedContentIds]));
            }
        } else if(!$this->streamQuery->isSingleContentQuery()) {
            // All pinned entries of this container were loaded within the initial request, so don't include them here!
            $this->query->andWhere(['OR', ['content.pinned' => 0], ['<>', 'content.contentcontainer_id', $this->container->contentcontainer_id]]);
        }
    }

    /**
     * Loads pinned content entries into [[pinnedContent]] by means of a cloned stream query.
     * @return array array of pinned content ids
     */
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
}

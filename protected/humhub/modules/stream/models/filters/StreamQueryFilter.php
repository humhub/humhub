<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\content\models\Content;
use humhub\modules\stream\models\ContentContainerStreamQuery;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\ui\filter\models\QueryFilter;
use humhub\modules\user\models\User;

abstract class StreamQueryFilter extends QueryFilter
{
    /**
     * @var StreamQuery|ContentContainerStreamQuery
     */
    public $streamQuery;

    /**
     * @inheritDoc
     */
    public $autoLoad = self::AUTO_LOAD_GET;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isLoaded && !parent::validate()) {
            $this->streamQuery->addErrors($this->getErrors());
        }
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return $this->formName ?: 'StreamQuery';
    }

    /**
     * This method allows the stream filter direct access to returned Content[] array.
     * e.g. additional entries can be injected
     *
     * @param Content[] $results
     */
    public function postProcessStreamResult(array &$results): void
    {
    }

    /**
     * @return bool
     * @since v1.15
     */
    public function allowPinContent(): bool
    {
        if (empty($this->streamQuery->pinnedContentSupport)) {
            return false;
        }

        return $this->streamQuery->isInitialQuery();
    }

    /**
     * Check if a not published content can be visible
     *
     * @return bool
     * @since v1.15
     */
    public function allowStateContent(): bool
    {
        if ($this->streamQuery instanceof ActivityStreamQuery && $this->streamQuery->activity) {
            // Don't display for activity stream
            return false;
        }

        if ($this->streamQuery instanceof ContentContainerStreamQuery
            && $this->streamQuery->container instanceof User
            && $this->streamQuery->user instanceof User
            && !$this->streamQuery->container->is($this->streamQuery->user)) {
            // Don't display from another user
            return false;
        }

        return true;
    }
}

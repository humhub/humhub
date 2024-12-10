<?php

namespace humhub\modules\user\stream;

use humhub\modules\stream\models\ContentContainerStreamQuery;
use humhub\modules\stream\models\filters\ContentContainerStreamFilter;
use humhub\modules\user\stream\filters\IncludeAllContributionsFilter;

/**
 * ProfileStream
 *
 * @package humhub\modules\user\components
 */
class ProfileStreamQuery extends ContentContainerStreamQuery
{
    /**
     * @var bool|null can be used to set a default state for the IncludeAllContributionsFilter
     */
    public $includeContributions;

    /**
     * @inheritdoc
     */
    public function beforeApplyFilters()
    {
        $this->removeFilterHandler(ContentContainerStreamFilter::class);

        // The default scope may be overwritten by first request, the real default is handled in the stream filter navigation
        $this->addFilterHandler(new IncludeAllContributionsFilter([
            'container' => $this->container,
            'scope' => $this->includeContributions
                ? IncludeAllContributionsFilter::SCOPE_ALL
                : IncludeAllContributionsFilter::SCOPE_PROFILE,
        ]));

        parent::beforeApplyFilters();
    }
}

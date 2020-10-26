<?php

namespace humhub\modules\dashboard\stream;

use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\dashboard\stream\filters\DashboardGuestStreamFilter;
use humhub\modules\dashboard\stream\filters\DashboardMemberStreamFilter;

/**
 * Class DashboardStreamQuery
 *
 * @since 1.8
 */
class DashboardStreamQuery extends ActivityStreamQuery
{
    /**
     * @inheritDoc
     */
    public $pinnedContentSupport = false;

    /**
     * @inheritDoc
     */
    public function beforeApplyFilters()
    {
        parent::beforeApplyFilters();

        if (empty($this->user)) {
            $this->addFilterHandler(DashboardGuestStreamFilter::class);
        } else {
            $this->addFilterHandler(new DashboardMemberStreamFilter(['user' => $this->user]));
        }
    }

}

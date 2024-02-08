<?php

namespace humhub\modules\dashboard\stream;

use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\dashboard\Module;
use Yii;

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
        if (empty($this->user)) {
            $this->addFilterHandler(Module::getModuleInstance()->guestFilterClass);
        } else {
            $this->addFilterHandler(Yii::createObject([
                'class' => Module::getModuleInstance()->memberFilterClass,
                'user' => $this->user]));
        }

        parent::beforeApplyFilters();
    }
}

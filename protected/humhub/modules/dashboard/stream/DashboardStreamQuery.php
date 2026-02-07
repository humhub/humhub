<?php

namespace humhub\modules\dashboard\stream;

use humhub\modules\dashboard\Module;
use humhub\modules\stream\models\ContentContainerStreamQuery;
use Yii;

/**
 * Class DashboardStreamQuery
 *
 * @since 1.8
 */
class DashboardStreamQuery extends ContentContainerStreamQuery
{
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

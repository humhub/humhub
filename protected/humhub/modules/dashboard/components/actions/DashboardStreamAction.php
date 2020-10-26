<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard\components\actions;

use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\dashboard\stream\DashboardStreamQuery;
use humhub\modules\dashboard\stream\filters\DashboardGuestStreamFilter;
use humhub\modules\dashboard\stream\filters\DashboardMemberStreamFilter;
use Yii;
use yii\db\Query;
use humhub\modules\dashboard\Module;
use humhub\modules\activity\actions\ActivityStreamAction;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use humhub\modules\content\models\Content;

/**
 * DashboardStreamAction
 *
 * Note: This stream action is also used for activity e-mail content.
 *
 * @since 0.11
 * @author luke
 */
class DashboardStreamAction extends ActivityStreamAction
{
    /**
     * @inheritDoc
     */
    public $activity = false;

    /**
     * @inheritDoc
     */
    public $streamQueryClass = DashboardStreamQuery::class;

    /**
     * @inheritDoc
     */
    public function initStreamEntryOptions()
    {
        return parent::initStreamEntryOptions()->viewContext(StreamEntryOptions::VIEW_CONTEXT_DASHBOARD);
    }
}

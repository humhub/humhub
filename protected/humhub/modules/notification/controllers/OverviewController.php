<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\notification\services\NotificationListService;
use Yii;

/**
 * The notification overview page.
 *
 * A Vue island (`NotificationOverview`): this controller only renders its mount point and hands
 * over what the server owns — the first page of notifications, the (module-defined, localized)
 * notification groups that can be filtered by, and the rendered icon markup. Filtering and paging
 * happen against the notification API from there
 * ({@see \humhub\modules\notification\controllers\api\NotificationController}).
 *
 * @since 0.5
 */
class OverviewController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'initial' => (new NotificationListService())->page(NotificationListService::OVERVIEW_PAGE_SIZE),
            'filters' => $this->getFilters(),
        ]);
    }

    /**
     * The notification groups the filter offers, in their sort order.
     *
     * @return array{id: string, title: string}[]
     */
    private function getFilters(): array
    {
        $result = [];

        foreach (Yii::$app->notification->getGroups(Yii::$app->user->getIdentity()) as $group) {
            $result[] = ['id' => $group->id, 'title' => $group->title];
        }

        return $result;
    }
}

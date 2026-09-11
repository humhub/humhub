<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\notification\services\NotificationWindowService;
use Yii;

/**
 * The notification overview page.
 *
 * A Vue island (`NotificationOverview`) since 1.19: this controller only renders its mount point
 * and hands over what the server owns — the first page of notifications, the (module-defined,
 * localized) categories that can be filtered by, and the rendered icon markup. Filtering and
 * paging happen against the notification API from there
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
            'initial' => (new NotificationWindowService())->window(NotificationWindowService::OVERVIEW_PAGE_SIZE),
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * The categories the filter offers, in the order and with the titles the server-rendered
     * checkbox list used — including the catch-all for notifications without a category of
     * their own.
     */
    private function getCategories(): array
    {
        $result = [];

        foreach (Yii::$app->notification->getNotificationCategories(Yii::$app->user->getIdentity()) as $category) {
            /** @var NotificationCategory $category */
            $result[] = ['id' => $category->id, 'title' => $category->getTitle()];
        }

        $result[] = [
            'id' => FilterForm::NO_CATEGORY_ID,
            'title' => Yii::t('NotificationModule.base', 'Others'),
        ];

        return $result;
    }
}

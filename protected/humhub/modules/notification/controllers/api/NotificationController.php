<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationListService;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * The notification API (see `docs/develop/concept-api.md`), consumed by the notification
 * islands: the top-menu dropdown and the overview page (`notification/vue/`).
 *
 * Always the caller's own notifications — there is no user parameter, and no action here is
 * reachable for a guest.
 *
 * The page itself is built by {@see NotificationListService}, which the widgets inlining a
 * first page into their island props use as well, so an embedded page and a fetched one are the
 * same thing — see that class for the cursor and the consistency handling.
 *
 * @since 1.20
 */
class NotificationController extends BaseController
{
    /**
     * @var int the largest page a client may ask for
     */
    public const MAX_LIMIT = 50;

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                    'mark-as-seen' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * The caller's notifications, newest entry first, one entry per group.
     *
     * Parameters: `cursor` (the previous page's `nextCursor`), `limit`, `categories[]` (notification
     * category ids, e.g. `direct`, `social`) and `seen` (`seen`/`unseen`). Without `categories`
     * nothing is filtered by category; with ids matching no category the list is empty, which is
     * what "no category selected" means.
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $limit = max(1, min(
            (int)$request->get('limit', NotificationListService::MENU_PAGE_SIZE),
            self::MAX_LIMIT,
        ));

        $categories = $request->get('categories');
        $cursor = $request->get('cursor');

        $seen = $request->get('seen', '');
        if (!is_string($seen) || ($seen !== '' && !in_array($seen, ['seen', 'unseen'], true))) {
            return $this->validationErrors([
                'seen' => [Yii::t('yii', '{attribute} is invalid.', ['attribute' => 'seen'])],
            ]);
        }

        return (new NotificationListService())->page(
            $limit,
            is_string($cursor) && $cursor !== '' ? $cursor : null,
            $categories === null ? null : self::listValues($categories),
            $seen ?: null,
        );
    }

    /**
     * Marks notifications of the caller as seen: with `ids[]` the entries (groups) of those
     * notifications - ids of other users' notifications are ignored -, without every one.
     */
    public function actionMarkAsSeen()
    {
        $user = Yii::$app->user->getIdentity();
        $ids = Yii::$app->request->post('ids');

        if ($ids === null) {
            NotificationManager::markAllSeen($user);
        } else {
            $ids = array_map('intval', self::listValues($ids));
            $records = $ids === [] ? [] : Notification::find()
                ->forUser($user)
                ->andWhere(['notification.id' => $ids])
                ->all();

            foreach ($records as $record) {
                NotificationManager::markRecordSeen($record);
            }
        }

        return ['unseenCount' => NotificationListService::unseenCount($user)];
    }
}

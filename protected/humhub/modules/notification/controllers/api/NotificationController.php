<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\notification\events\UnreadCountChangedEvent;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationWindowService;
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
 * The page itself is built by {@see NotificationWindowService}, which the widgets inlining a
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
    protected bool $enableSessionAuth = true;

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
     * The caller's notifications, unseen first, newest first.
     *
     * Parameters: `cursor`, `limit`, `categories[]` (notification category ids) and `seen`
     * (`seen`/`unseen`). Without `categories` nothing is filtered by category; with an entry
     * matching no category the list is empty, which is what "no category selected" means.
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $limit = max(1, min(
            (int)$request->get('limit', NotificationWindowService::MENU_PAGE_SIZE),
            self::MAX_LIMIT,
        ));

        $categories = $request->get('categories');
        $categories = is_array($categories)
            ? array_values(array_filter($categories, 'is_string'))
            : null;

        return (new NotificationWindowService())->window(
            $limit,
            (int)$request->get('cursor', 0) ?: null,
            $categories,
            (string)$request->get('seen', '') ?: null,
        );
    }

    /**
     * Marks every notification of the caller as seen.
     */
    public function actionMarkAsSeen()
    {
        $count = Notification::updateAll(['seen' => 1], ['user_id' => Yii::$app->user->id]);

        if ($count > 0) {
            UnreadCountChangedEvent::triggerChanged(Yii::$app->user->getIdentity());
        }

        return ['unseenCount' => 0];
    }
}

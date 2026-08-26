<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\activity\services\ActivityWindowService;
use humhub\modules\content\models\ContentContainer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * The activity API (see `docs/develop/concept-api.md`), consumed by the `ActivityBox` island
 * (`activity/vue/`).
 *
 * Always the activities the caller may see: without `containerGuid` those of every container
 * they subscribe to (the dashboard box), with it those of that one container (the box in a
 * space or on a profile). Which activities that is stays a decision of the query scopes
 * (`ActiveQueryActivity::defaultScopes()`/`contentContainer()`/`subscribedContentContainers()`)
 * — this endpoint adds no visibility rule of its own, and an activity in a container the caller
 * cannot see simply does not appear.
 *
 * The page itself is built by {@see ActivityWindowService}, which the widget inlining a first
 * page into its island props uses as well, so an embedded page and a fetched one are the same
 * thing — see that class for the cursor.
 *
 * @since 1.20
 */
class ActivityController extends BaseController
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
                ],
            ],
        ]);
    }

    /**
     * The activities the caller may see, newest group first.
     *
     * Parameters: `containerGuid` (a space or user guid), `limit` and `cursor` (the previous
     * page's `nextCursor`).
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $limit = max(1, min(
            (int)$request->get('limit', ActivityWindowService::PAGE_SIZE),
            self::MAX_LIMIT,
        ));

        return (new ActivityWindowService())->window(
            $limit,
            (string)$request->get('cursor', '') ?: null,
            $this->findContainer((string)$request->get('containerGuid', '')),
        );
    }

    /**
     * The container to scope to, `null` for the dashboard view.
     *
     * A guid naming no container is a 404 — a caller asking for a specific container should
     * learn that it does not exist rather than receive the dashboard's activities. Whether the
     * caller may see anything IN it is left to the query scopes, so an existing container the
     * caller has no access to answers an empty list and reveals nothing.
     *
     * @throws NotFoundHttpException
     */
    private function findContainer(string $guid): ?ContentContainer
    {
        if ($guid === '') {
            return null;
        }

        $container = ContentContainer::findOne(['guid' => $guid]);

        if ($container === null) {
            throw new NotFoundHttpException('Content container not found!');
        }

        return $container;
    }
}

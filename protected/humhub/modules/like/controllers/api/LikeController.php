<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\controllers\api;

use humhub\components\api\BaseController;
use humhub\models\RecordMap;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\like\serializers\LikeSerializer;
use humhub\modules\like\services\LikeService;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\UserSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The like API (see `docs/develop/concept-api.md`), consumed by the like Vue island and
 * available to any API client.
 *
 * A record is addressed either by `recordId` (the platform-wide record id, as carried by
 * every serialized record that can be liked) or by `model` + `pk`. All three write/read
 * actions answer the same state shape, so a client never has to derive one value from
 * another (see {@see LikeSerializer::state()}).
 *
 * @since 1.19
 */
class LikeController extends BaseController
{
    /**
     * @var int how many records one batched state request covers, see
     * {@see self::getRequestedRecordIds()}
     */
    public const MAX_BATCH_SIZE = 100;

    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

    /**
     * @inheritdoc
     *
     * Guests may read the like state and the list of likers of content they can see;
     * `liked`/`canLike` are always `false` for them.
     */
    protected array $guestAllowedActions = ['state', 'states', 'users'];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'state' => ['GET', 'HEAD'],
                    'states' => ['GET', 'HEAD'],
                    'users' => ['GET', 'HEAD'],
                    'create' => ['POST'],
                    'remove' => ['DELETE'],
                ],
            ],
        ]);
    }

    /**
     * The caller's like state of a record.
     */
    public function actionState()
    {
        return LikeSerializer::state(new LikeService($this->findRecord()));
    }

    /**
     * The caller's like state of MANY records at once, keyed by record id.
     *
     * The point of this endpoint is that a like state is the one thing about a record that is
     * both per-record and per-caller: it is what keeps a comment window (or a stream page)
     * from being a single, cacheable representation. Serving it separately lets those payloads
     * be caller-neutral, and serving it in one batch keeps that from costing a request - or a
     * query - per record: counts and the caller's own likes are two grouped queries for the
     * whole set (see {@see LikeService::preloadState()}).
     *
     * Records the caller may not see are absent from the result rather than failing the whole
     * request - a client asking for a window it just received should not lose the whole
     * response because one record became invisible in between.
     */
    public function actionStates()
    {
        $recordIds = $this->getRequestedRecordIds();

        if ($recordIds === []) {
            return ['results' => (object)[]];
        }

        $records = RecordMap::getByIds($recordIds, ContentProvider::class, ['content']);
        $records = array_filter($records, fn(ContentProvider $record) => $record->content->canView());
        $results = LikeSerializer::statesForRecords($records);

        // (object) so an empty map serializes as `{}` rather than `[]`, and so numeric record
        // ids stay object keys instead of turning into array indices.
        return ['results' => $results === [] ? (object)[] : (object)$results];
    }

    /**
     * Likes a record; 403 unless the caller may like it.
     */
    public function actionCreate()
    {
        $likeService = new LikeService($this->findRecord());

        if (!$likeService->canLike()) {
            throw new ForbiddenHttpException();
        }

        $likeService->like();

        return LikeSerializer::state($likeService);
    }

    /**
     * Removes the caller's like of a record. Idempotent — unliking something that was never
     * liked is a success, not an error.
     */
    public function actionRemove()
    {
        $likeService = new LikeService($this->findRecord());
        $likeService->unlike();

        return LikeSerializer::state($likeService);
    }

    /**
     * The users who liked a record, newest first, as a paginated list of user shapes.
     */
    public function actionUsers()
    {
        $likeService = new LikeService($this->findRecord());

        $query = $likeService->getUserQuery();
        $pagination = $this->handlePagination($query);

        return $this->returnPagination(
            $pagination,
            array_map(fn(User $user) => UserSerializer::short($user), $query->all()),
        );
    }

    /**
     * Resolves the addressed record from `recordId` or `model` + `pk`.
     *
     * @throws NotFoundHttpException for an unknown record
     * @throws ForbiddenHttpException when the caller cannot see the record's content
     */
    protected function findRecord(): ContentProvider
    {
        $request = Yii::$app->request;
        $recordId = $request->get('recordId', $request->getBodyParam('recordId'));

        $record = $recordId
            ? RecordMap::getById((int)$recordId, ContentProvider::class)
            : RecordMap::getByModelAndPk(
                (string)$request->get('model', $request->getBodyParam('model', '')),
                (string)$request->get('pk', $request->getBodyParam('pk', '')),
                ContentProvider::class,
            );

        if (!$record) {
            throw new NotFoundHttpException();
        }

        if (!$record->content->canView()) {
            throw new ForbiddenHttpException();
        }

        return $record;
    }

    /**
     * The `recordIds` of a batch request - comma-separated or repeated (`recordIds[]=`).
     *
     * Capped rather than rejected: a client asking for more than a page worth of records gets
     * the first {@see self::MAX_BATCH_SIZE} instead of an error it cannot act on, and learns
     * from the missing keys that it has to ask again.
     *
     * @return int[]
     */
    protected function getRequestedRecordIds(): array
    {
        $requested = Yii::$app->request->get('recordIds', '');
        $requested = is_array($requested) ? $requested : explode(',', (string)$requested);

        $recordIds = [];
        foreach ($requested as $recordId) {
            $recordId = (int)trim((string)$recordId);
            if ($recordId > 0) {
                $recordIds[$recordId] = $recordId;
            }
        }

        return array_slice(array_values($recordIds), 0, self::MAX_BATCH_SIZE);
    }
}

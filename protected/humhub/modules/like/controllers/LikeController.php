<?php

namespace humhub\modules\like\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\models\RecordMap;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\like\services\LikeService;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserJsonService;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class LikeController extends Controller
{
    private LikeService $likeService;

    public function beforeAction($action): bool
    {

        $recordId = (int)Yii::$app->request->get('recordId');
        $target = RecordMap::getById($recordId, ContentProvider::class);

        if (!$target) {
            throw new NotFoundHttpException();
        }

        if (!$target->content->canView()) {
            throw new ForbiddenHttpException();
        }

        $this->likeService = new LikeService($target);
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['info'],
            ],
        ];
    }

    public function actionLike()
    {
        $this->forcePostRequest();


        if (!$this->likeService->canLike()) {
            throw new ForbiddenHttpException();
        }

        $this->likeService->like();

        return $this->asJson([
            'currentUserLiked' => $this->likeService->hasLiked(),
            'likeCounter' => $this->likeService->getCount(),
        ]);
    }

    public function actionUnlike()
    {
        $this->forcePostRequest();
        $this->likeService->unlike();

        return $this->asJson([
            'currentUserLiked' => $this->likeService->hasLiked(),
            'likeCounter' => $this->likeService->getCount(),
        ]);
    }

    /**
     * Returns the current like state of the record.
     *
     * @since 1.19
     */
    public function actionInfo()
    {
        return $this->asJson([
            'currentUserLiked' => $this->likeService->hasLiked(),
            'likeCounter' => $this->likeService->getCount(),
        ]);
    }

    /**
     * Returns a page of the users who liked the record, for the Vue `<UserList>`
     * user-list modal in `LikeButton.vue`.
     *
     * Replaces the legacy HTML action (which rendered `user\widgets\UserListBox`
     * into the global modal) - module-search found no external usage of this route,
     * see `docs/develop/module-migrate.md`. Visibility mirrors the legacy behavior
     * exactly: any user who can view the content (enforced in `beforeAction()`) sees
     * every liker, with no additional blocked-user masking - `LikeService::getUserQuery()`
     * never applied any either.
     *
     * `limit` is clamped to [1, userListPaginationSize] - same guest/member-reachable
     * "non-positive LIMIT is dropped entirely by the query builder" concern
     * `CommentJsonService::clampPageSize()` guards against.
     *
     * @since 1.19
     */
    public function actionUserList()
    {
        $defaultLimit = Yii::$app->getModule('user')->userListPaginationSize;
        $limit = max(1, min((int)Yii::$app->request->get('limit', $defaultLimit), $defaultLimit));
        $page = max(1, (int)Yii::$app->request->get('page', 1));

        $query = $this->likeService->getUserQuery();
        $total = (clone $query)->count();
        $users = $query->offset(($page - 1) * $limit)->limit($limit)->all();
        $hasMore = ($page * $limit) < $total;

        $userJsonService = new UserJsonService();

        return $this->asJson([
            'total' => $total,
            'users' => array_map(fn(User $user) => $userJsonService->serialize($user), $users),
            'hasMore' => $hasMore,
            'nextPage' => $hasMore ? $page + 1 : null,
        ]);
    }
}

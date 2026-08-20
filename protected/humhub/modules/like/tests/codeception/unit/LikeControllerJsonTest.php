<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\like;

use humhub\components\behaviors\AccessControl;
use humhub\models\RecordMap;
use humhub\modules\like\controllers\LikeController;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Thin tests for `LikeController::actionUserList()` - the JSON reimplementation of
 * the legacy `user\widgets\UserListBox` HTML action, following the
 * `Yii::$app->runAction()` pattern {@see \tests\codeception\unit\modules\comment\controllers\CommentControllerJsonTest}
 * already established for JSON actions.
 *
 * @since 1.19
 */
class LikeControllerJsonTest extends HumHubDbTestCase
{
    public function testActionUserListReturnsLikersInAuthorShape()
    {
        $post = Post::findOne(['id' => 2]);

        $this->becomeUser('User1');
        $this->assertTrue((new LikeService($post))->like());

        $this->becomeUser('User2');
        $this->assertTrue((new LikeService($post))->like());

        $this->get(['recordId' => RecordMap::getId($post)]);
        $response = Yii::$app->runAction('like/like/user-list');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $this->assertSame(2, $response->data['total']);
        $this->assertCount(2, $response->data['users']);
        $this->assertFalse($response->data['hasMore']);
        $this->assertNull($response->data['nextPage']);

        $this->assertSame(
            ['guid', 'displayName', 'url', 'imageUrl', 'contentContainerId', 'imageAlt', 'online'],
            array_keys($response->data['users'][0]),
        );
    }

    public function testActionUserListPaginatesAndClampsLimitToTheConfiguredMax()
    {
        $post = Post::findOne(['id' => 2]);

        $this->becomeUser('User1');
        $this->assertTrue((new LikeService($post))->like());
        $this->becomeUser('User2');
        $this->assertTrue((new LikeService($post))->like());

        $userModule = Yii::$app->getModule('user');
        $originalPageSize = $userModule->userListPaginationSize;
        $userModule->userListPaginationSize = 1;

        try {
            // A requested limit above the configured max is clamped down to it - same
            // guest/member-reachable "unbounded LIMIT" concern
            // CommentJsonService::clampPageSize() guards against.
            $this->get(['recordId' => RecordMap::getId($post), 'limit' => 100]);
            // `asJson()` returns the shared `Yii::$app->response` singleton, not a fresh
            // object - its ->data is captured into a plain array immediately, before the
            // second runAction() call below overwrites that same object's ->data.
            $firstPage = Yii::$app->runAction('like/like/user-list')->data;

            $this->assertCount(1, $firstPage['users']);
            $this->assertSame(2, $firstPage['total']);
            $this->assertTrue($firstPage['hasMore']);
            $this->assertSame(2, $firstPage['nextPage']);

            $this->get(['recordId' => RecordMap::getId($post), 'page' => $firstPage['nextPage']]);
            $secondPage = Yii::$app->runAction('like/like/user-list')->data;

            $this->assertCount(1, $secondPage['users']);
            $this->assertFalse($secondPage['hasMore']);
            $this->assertNull($secondPage['nextPage']);

            $this->assertNotSame($firstPage['users'][0]['guid'], $secondPage['users'][0]['guid']);
        } finally {
            $userModule->userListPaginationSize = $originalPageSize;
        }
    }

    public function testActionUserListNotFoundForAnInvalidRecordId()
    {
        $this->becomeUser('User2');
        $this->get(['recordId' => 999999999]);

        $this->expectException(NotFoundHttpException::class);
        Yii::$app->runAction('like/like/user-list');
    }

    /**
     * Mirrors the legacy behavior exactly: the HTML `actionUserList` was never
     * guest-allowed (only `info` was) - the JSON reimplementation must not
     * accidentally loosen that.
     */
    public function testUserListIsNotGuestAllowedLikeTheLegacyAction()
    {
        $controller = new LikeController('like', Yii::$app->getModule('like'));
        $behaviors = $controller->behaviors();

        $this->assertInstanceOf(AccessControl::class, new $behaviors['acl']['class']());
        $this->assertSame(['info'], $behaviors['acl']['guestAllowedActions']);
    }

    private function get(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Yii::$app->request->setQueryParams($params);
        Yii::$app->request->setBodyParams([]);
    }
}

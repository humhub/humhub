<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\controllers;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\file\models\File;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Thin tests for the JSON actions of CommentController, following the
 * `Yii::$app->runAction()` pattern used by AddGroupMemberTest: the actual
 * serialization/pagination behavior is covered by CommentJsonServiceTest.
 */
class CommentControllerJsonTest extends HumHubDbTestCase
{
    public function testActionListReturnsWindow()
    {
        $this->becomeUser('User2');
        $this->createComment('Comment A');
        $this->createComment('Comment B');

        $this->get(['contentId' => 9]);
        $response = Yii::$app->runAction('comment/comment/list');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $this->assertCount(2, $response->data['comments']);
        $this->assertSame(2, $response->data['total']);
    }

    public function testActionInfoReturnsSingleComment()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Comment info');

        $this->get(['id' => $comment->id]);
        $response = Yii::$app->runAction('comment/comment/info');

        $this->assertSame($comment->id, $response->data['id']);
    }

    public function testActionCreateSavesCommentAndReturnsJson()
    {
        $this->becomeUser('User2');

        $this->post(['contentId' => 9, 'message' => 'New comment via JSON API']);
        $response = Yii::$app->runAction('comment/comment/create');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $this->assertNotEmpty($response->data['id']);
        $this->assertNotNull(Comment::findOne(['id' => $response->data['id']]));
    }

    public function testActionCreateValidationFailureReturns422()
    {
        $this->becomeUser('User2');

        $this->post(['contentId' => 9, 'message' => '']);
        $response = Yii::$app->runAction('comment/comment/create');

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('errors', $response->data);
    }

    public function testActionCreateRejectsSecondNestingLevel()
    {
        $this->becomeUser('User2');
        $root = $this->createComment('Root');
        $reply = $this->createComment('Reply', $root);

        $this->post(['contentId' => 9, 'parentCommentId' => $reply->id, 'message' => 'Too deep']);
        $response = Yii::$app->runAction('comment/comment/create');

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('parentCommentId', $response->data['errors']);
    }

    public function testActionUpdateGetReturnsRawMessageForOwner()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Editable message');

        $this->get(['id' => $comment->id]);
        $response = Yii::$app->runAction('comment/comment/update');

        $this->assertSame(['message' => 'Editable message'], $response->data);
    }

    public function testActionUpdatePostSavesCommentAndReturnsJson()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Original');

        $this->post(['id' => $comment->id, 'message' => 'Changed']);
        $response = Yii::$app->runAction('comment/comment/update');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $this->assertSame($comment->id, $response->data['id']);
        $this->assertStringContainsString('Changed', $response->data['messageOutput']);
    }

    public function testActionUpdateForbiddenForNonOwner()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Owned by User2');

        $this->becomeUser('User3');
        $this->get(['id' => $comment->id]);

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('comment/comment/update');
    }

    public function testActionUpdateValidationFailureReturns422()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Original');

        $this->post(['id' => $comment->id, 'message' => '']);
        $response = Yii::$app->runAction('comment/comment/update');

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('errors', $response->data);
    }

    public function testActionCreateAttachesFileList()
    {
        $this->becomeUser('User2');
        $file = $this->createFile();

        $this->post(['contentId' => 9, 'message' => 'With attachment', 'fileList' => [$file->guid]]);
        $response = Yii::$app->runAction('comment/comment/create');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $comment = Comment::findOne(['id' => $response->data['id']]);
        $file->refresh();
        $this->assertTrue($file->isAssignedTo($comment));
    }

    public function testActionUpdateAttachesFileList()
    {
        $this->becomeUser('User2');
        $comment = $this->createComment('Original');
        $file = $this->createFile();

        $this->post(['id' => $comment->id, 'message' => 'Updated with attachment', 'fileList' => [$file->guid]]);
        $response = Yii::$app->runAction('comment/comment/update');

        $this->assertSame(200, Yii::$app->response->statusCode);
        $file->refresh();
        $this->assertTrue($file->isAssignedTo($comment));
    }

    public function testActionCreateForbiddenWhenCommentsAreLocked()
    {
        $this->becomeUser('User2');
        Content::findOne(['id' => 9])->updateAttributes(['locked_comments' => 1]);

        $this->post(['contentId' => 9, 'message' => 'Should be blocked']);

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('comment/comment/create');
    }

    private function createFile(): File
    {
        $file = new File();
        $file->file_name = 'test.txt';
        $this->assertTrue($file->save());

        return $file;
    }

    private function createComment(string $message, ?Comment $parent = null): Comment
    {
        $comment = new Comment([
            'message' => $message,
            'content_id' => 9,
            'parent_comment_id' => $parent?->id,
        ]);
        $this->assertTrue($comment->save());

        return $comment;
    }

    private function get(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Yii::$app->request->setQueryParams($params);
        Yii::$app->request->setBodyParams([]);
    }

    private function post(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->request->setQueryParams([]);
        Yii::$app->request->setBodyParams($params);
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\controllers;

use humhub\modules\content\models\Content;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * `actionShow` only has one remaining mode: rendering the comment section (a `<comment-section>`
 * island, see `humhub\modules\comment\widgets\Comments`) inside a modal for
 * `CommentLink::MODE_POPUP` (used by `ContentObjectLinks`). Pagination/window semantics are
 * covered at the widget/service level by CommentsWidgetTest/CommentJsonServiceTest.
 */
class CommentControllerShowTest extends HumHubDbTestCase
{
    public function testPopupModeRendersCommentIsland()
    {
        $this->becomeUser('User2');
        $this->get(['contentId' => 9, 'mode' => 'popup']);

        $html = Yii::$app->runAction('comment/comment/show');

        $this->assertStringContainsString('<comment-section', $html);
        $this->assertStringContainsString('content-id="9"', $html);
    }

    public function testNonPopupModeIsNoLongerSupported()
    {
        $this->becomeUser('User2');
        $this->get(['contentId' => 9]);

        $this->expectException(NotFoundHttpException::class);
        Yii::$app->runAction('comment/comment/show');
    }

    public function testPopupModeWithParentCommentScopesToReplies()
    {
        $this->becomeUser('User2');
        $content = Content::findOne(['id' => 9]);
        $parent = new \humhub\modules\comment\models\Comment(['message' => 'Root', 'content_id' => $content->id]);
        $this->assertTrue($parent->save());

        $this->get(['parentCommentId' => $parent->id, 'mode' => 'popup']);

        $html = Yii::$app->runAction('comment/comment/show');

        $this->assertStringContainsString('<comment-section', $html);
        $this->assertStringContainsString('id="comment_C' . $content->id . 'P' . $parent->id . '"', $html);
    }

    private function get(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/comment/comment/show?' . http_build_query($params);
        Yii::$app->request->setQueryParams($params);
        Yii::$app->request->setBodyParams([]);
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\controllers;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * `actionForm` hands a `CommentSection` island mounted by `contentId` alone the comment form's
 * props (`Comments::formProps()`), the same the widget embeds.
 */
class CommentControllerFormTest extends HumHubDbTestCase
{
    public function testHandsTheFormToWhoMayComment()
    {
        $this->becomeUser('User2');
        $this->get(['contentId' => 9]);

        $props = $this->props(Yii::$app->runAction('comment/comment/form'));

        $this->assertTrue($props['canComment']);
        $this->assertStringContainsString('__VUEFORM__', $props['formShellHtml']);
        $this->assertStringContainsString('user%2Fmentioning%2Fcontent', $props['formShellHtml']);
        $this->assertArrayHasKey('max', $props['uploadOptions']);
        $this->assertNotEmpty($props['submitIconHtml']);
    }

    public function testRefusesContentTheUserCannotSee()
    {
        // Content 1 is Admin's private profile post, which User1 cannot see
        $this->becomeUser('User1');
        $this->get(['contentId' => 1]);

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('comment/comment/form');
    }

    private function props(Response $response): array
    {
        $this->assertSame(Response::FORMAT_JSON, $response->format);

        return $response->data;
    }

    private function get(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/comment/comment/form?' . http_build_query($params);
        Yii::$app->request->setQueryParams($params);
        Yii::$app->request->setBodyParams([]);
    }
}

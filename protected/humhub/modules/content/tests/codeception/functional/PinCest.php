<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

class PinCest
{
    public function testPinLimitMessageIsPlainText(FunctionalTester $I)
    {
        $I->wantTo('ensure the pin limit message contains no markup, the toast shows it as text');

        $I->amAdmin();
        $first = $this->createSpacePost('First');
        $second = $this->createSpacePost('Second');

        $module = Yii::$app->getModule('content');
        $maxPinned = $module->maxPinnedSpaceContent;
        $module->maxPinnedSpaceContent = 1;

        try {
            $I->sendAjaxPostRequest(Url::to(['/content/content/pin', 'id' => $first->content->id]));
            Assert::assertTrue(json_decode($I->grabPageSource(), true)['success']);

            $I->sendAjaxPostRequest(Url::to(['/content/content/pin', 'id' => $second->content->id]));
            Assert::assertSame([
                'success' => false,
                'info' => 'Maximum number of pinned items reached! You can only pin up to 1 items at once.',
            ], json_decode($I->grabPageSource(), true));
        } finally {
            $module->maxPinnedSpaceContent = $maxPinned;
        }
    }

    private function createSpacePost(string $message): Post
    {
        $post = new Post();
        $post->message = $message;
        $post->content->setContainer(Space::findOne(['id' => 3]));
        Assert::assertTrue($post->save());

        return $post;
    }
}

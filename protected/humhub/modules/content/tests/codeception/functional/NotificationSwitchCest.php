<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use PHPUnit\Framework\Assert;
use yii\helpers\Url;

class NotificationSwitchCest
{
    public function testDenyNotificationSwitchForUnreadablePrivateSpaceContent(FunctionalTester $I)
    {
        $I->wantTo('ensure non-members cannot follow private space content by direct notification switch requests');

        $I->amAdmin();
        $post = $this->createPrivateSpacePost();
        $I->logout();
        $I->amUser3();
        $I->sendAjaxPostRequest(Url::to(['/content/content/notification-switch', 'id' => $post->content->id, 'switch' => 1]));
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['success' => false], json_decode($I->grabPageSource(), true));
        $I->dontSeeRecord(Follow::class, [
            'object_model' => Post::class,
            'object_id' => $post->id,
            'user_id' => 4,
        ]);
    }

    public function testAllowNotificationSwitchForReadablePrivateSpaceContent(FunctionalTester $I)
    {
        $I->wantTo('ensure members can still follow private space content');

        $I->amAdmin();
        $post = $this->createPrivateSpacePost();
        $I->logout();
        $I->amUser1();
        $I->sendAjaxPostRequest(Url::to(['/content/content/notification-switch', 'id' => $post->content->id, 'switch' => 1]));
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['success' => true], json_decode($I->grabPageSource(), true));
        $I->seeRecord(Follow::class, [
            'object_model' => Post::class,
            'object_id' => $post->id,
            'user_id' => 2,
        ]);
    }

    private function createPrivateSpacePost(): Post
    {
        $space = Space::findOne(['id' => 3]);
        $space->visibility = Space::VISIBILITY_NONE;
        $space->save(false);

        $post = new Post();
        $post->message = 'Private Space Post';
        $post->content->setContainer($space);
        $post->content->visibility = Content::VISIBILITY_PRIVATE;
        Assert::assertTrue($post->save());

        return $post;
    }
}

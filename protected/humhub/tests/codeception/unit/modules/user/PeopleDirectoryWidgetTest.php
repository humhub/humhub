<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\user;

use humhub\modules\friendship\assets\FriendshipVueAsset;
use humhub\modules\user\assets\UserVueAsset;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\widgets\HeaderControlsMenu;
use humhub\modules\user\widgets\PeopleDirectory;
use humhub\modules\user\widgets\ProfileHeaderCounterSet;
use humhub\modules\user\widgets\UserFollowButton;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Json;

/**
 * The People directory page as a `<people-directory>` island, and the `UserFollowButton` island.
 *
 * @since 1.20
 */
class PeopleDirectoryWidgetTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByEmail', 1);
    }

    private function props(string $html): array
    {
        $this->assertMatchesRegularExpression('/\sprops="([^"]+)"/', $html, 'the complex props are JSON-encoded into a props attribute');
        preg_match('/\sprops="([^"]+)"/', $html, $matches);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }

    public function testRendersTheIslandWithItsPlaceholder(): void
    {
        $this->becomeUser('User2');

        $html = PeopleDirectory::widget();

        $this->assertStringContainsString('<people-directory', $html);
        $this->assertStringContainsString('</people-directory>', $html);
        $this->assertStringContainsString('class="c-page-toolbar"', $html);
        $this->assertStringContainsString('<h1 id="people-directory-placeholder-title" class="c-page-toolbar__title">People</h1>', $html);
        $this->assertStringContainsString('title="Invite new people"', $html, 'the toolbar actions stand in the placeholder already');
        $this->assertSame(12, substr_count($html, 'class="c-card-skeleton c-entity-card-skeleton c-people-card-skeleton"'));
        $this->assertStringNotContainsString('card-panel', $html);

        $bundles = Yii::$app->view->assetBundles;
        $this->assertArrayHasKey(UserVueAsset::class, $bundles);
        $this->assertArrayHasKey(FriendshipVueAsset::class, $bundles, 'the card nests the FriendshipButton');
    }

    public function testProps(): void
    {
        $this->becomeUser('User2');

        $html = PeopleDirectory::widget();
        $props = $this->props($html);

        $this->assertSame(['q', 'groupId', 'sort', 'scope'], array_column($props['filters'], 'key'));

        $this->assertSame(['invite-people-button'], array_column($props['actions'], 'id'));
        $this->assertTrue($props['actions'][0]['modal']);
        $this->assertSame('accent', $props['actions'][0]['variant']);

        $this->assertSame('btn btn-primary', $props['buttons']['friendClass']);
        $this->assertSame('btn btn-light', $props['buttons']['friendStateClass']);
        $this->assertSame('btn btn-accent', $props['buttons']['followClass']);
        $this->assertSame('btn btn-light', $props['buttons']['followingClass']);
        $this->assertSame('btn btn-light', $props['buttons']['placeholderClass']);

        // Scalar props travel as attributes of their own.
        $this->assertStringContainsString(' follow-enabled="true"', $html);
        $this->assertStringContainsString(' friendship-enabled="true"', $html);

        $this->assertStringContainsString('ti-check', $props['icons']['check']);
        $this->assertSame(['check', 'plus', 'clock', 'times'], array_keys($props['icons']));
    }

    public function testFeatureFlags(): void
    {
        $this->becomeUser('User2');
        Yii::$app->getModule('friendship')->settings->set('enable', 0);
        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;

        try {
            $html = PeopleDirectory::widget();
        } finally {
            $module->disableFollow = false;
        }

        $this->assertStringContainsString(' follow-enabled="false"', $html);
        $this->assertStringContainsString(' friendship-enabled="false"', $html);
        $this->assertNotContains('scope', array_column($this->props($html)['filters'], 'key'));
    }

    public function testNoInviteActionWithoutInvitations(): void
    {
        $this->becomeUser('User2');
        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByEmail', 0);
        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 0);

        $html = PeopleDirectory::widget();

        $this->assertSame([], array_column($this->props($html)['actions'], 'id'));
        $this->assertStringNotContainsString('c-page-toolbar__actions', $html);
    }

    public function testFollowButtonIsland(): void
    {
        $this->becomeUser('User2');
        $user = User::findOne(['id' => 2]);
        $user->unfollow();

        $html = UserFollowButton::widget(['user' => $user, 'followOptions' => ['class' => 'btn btn-accent btn-sm']]);

        $this->assertStringContainsString('<user-follow-button', $html);
        $this->assertStringContainsString(' user-id="2"', $html);
        $this->assertStringContainsString(' user-name="Peter Tester"', $html);
        $this->assertStringContainsString(' follow-class="btn btn-accent btn-sm"', $html);
        $this->assertStringContainsString(' following-class="btn btn-outline-primary"', $html);
        $initial = $this->props($html)['initial'];
        $this->assertFalse($initial['isFollowing']);
        $this->assertTrue($initial['canFollow']);
        $this->assertIsInt($initial['followerCount']);
    }

    public function testNoFollowButtonForOneselfGuestsOrADisabledFeature(): void
    {
        $this->becomeUser('User1');
        $this->assertSame('', UserFollowButton::widget(['user' => User::findOne(['id' => 2])]), 'oneself');

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;
        $other = User::findOne(['id' => 3]);
        $other->unfollow();
        try {
            $this->assertSame('', UserFollowButton::widget(['user' => $other]), 'nothing to offer');
        } finally {
            $module->disableFollow = false;
        }

        $this->logout();
        $this->assertSame('', UserFollowButton::widget(['user' => $other]), 'guest');
    }

    public function testTheHeaderControlsMenuFollowsThroughTheIsland(): void
    {
        $this->becomeUser('User1');
        $other = User::findOne(['id' => 3]);
        $other->unfollow();

        $html = HeaderControlsMenu::widget(['user' => $other]);

        $this->assertStringContainsString('<user-follow-button', $html, 'the follow entry is the UserFollowButton island');
        $this->assertStringContainsString(' follow-class="dropdown-item"', $html);
        $this->assertStringContainsString(' following-class="dropdown-item"', $html);
        $this->assertStringContainsString('ti-send', $html, 'the "Follow" state has an icon, as the menu entries do');
        $this->assertStringNotContainsString('profile/follow', $html);
        $this->assertStringNotContainsString('profile/unfollow', $html);
        $this->assertFalse($this->props($html)['initial']['isFollowing']);
    }

    public function testTheHeaderControlsMenuHasNoFollowEntryWithoutAnythingToOffer(): void
    {
        $this->becomeUser('User1');
        $other = User::findOne(['id' => 3]);
        $other->unfollow();

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $module->disableFollow = true;
        try {
            $this->assertStringNotContainsString('<user-follow-button', HeaderControlsMenu::widget(['user' => $other]));
        } finally {
            $module->disableFollow = false;
        }

        // The friendship system off, the header shows the follow button itself.
        Yii::$app->getModule('friendship')->settings->set('enable', 0);
        $this->assertStringNotContainsString('<user-follow-button', HeaderControlsMenu::widget(['user' => $other]));
    }

    public function testTheHeaderFollowerCounterNamesItsUser(): void
    {
        $this->becomeUser('User1');
        $html = ProfileHeaderCounterSet::widget(['user' => User::findOne(['id' => 3])]);

        // What humhub.user.js updates on `user:follow-changed` of that user.
        $this->assertMatchesRegularExpression('/<a [^>]*data-user-follower-count="3"[^>]*>\s*<div class="float-start entry">\s*<span class="count[^"]*">/', $html);
        $this->assertSame(1, substr_count($html, 'data-user-follower-count'), 'the followers counter only');
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\User;
use humhub\modules\user\services\UserJsonService;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Covers the shared user/author JSON shape - originally
 * `CommentJsonService::serializeAuthor()`, extracted here so the like module's
 * user-list endpoint (and any future caller) gets the exact same payload without
 * duplicating it. {@see \tests\codeception\unit\modules\comment\CommentJsonServiceTest}
 * pins that the comment module's own payload stayed byte-identical after the
 * extraction.
 *
 * @since 1.19
 */
class UserJsonServiceTest extends HumHubDbTestCase
{
    public function testSerializeReturnsTheExpectedShape(): void
    {
        $this->becomeUser('User1');
        $other = User::findOne(['username' => 'User2']);

        $data = (new UserJsonService())->serialize($other);

        $this->assertSame(
            ['guid', 'displayName', 'url', 'imageUrl', 'contentContainerId', 'imageAlt', 'online'],
            array_keys($data),
        );
        $this->assertSame($other->guid, $data['guid']);
        $this->assertSame($other->displayName, $data['displayName']);
        $this->assertSame($other->getUrl(), $data['url']);
        $this->assertSame($other->getProfileImage()->getUrl(), $data['imageUrl']);
        $this->assertSame($other->contentcontainer_id, $data['contentContainerId']);
        $this->assertStringContainsString($other->displayName, $data['imageAlt']);
    }

    public function testOnlineIsNullWhenSerializingTheViewerThemself(): void
    {
        $self = $this->becomeUser('User1');

        $data = (new UserJsonService())->serialize($self);

        $this->assertNull($data['online']);
    }

    public function testOnlineIsNullWhenTheFeatureIsDisabled(): void
    {
        $this->becomeUser('User1');
        $other = User::findOne(['username' => 'User2']);

        $module = \Yii::$app->getModule('user');
        $original = $module->settings->get('auth.hideOnlineStatus');
        $module->settings->set('auth.hideOnlineStatus', 1);

        try {
            $data = (new UserJsonService())->serialize($other);
            $this->assertNull($data['online']);
        } finally {
            $module->settings->set('auth.hideOnlineStatus', $original);
        }
    }
}

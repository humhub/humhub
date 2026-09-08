<?php

namespace tests\codeception\unit\modules\friendship;

use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Json;

/**
 * FriendshipButton has the same option round trip as MembershipButton: the button is
 * re-rendered after a friendship change and `RequestController::getActionResult()` feeds the
 * posted options straight into the widget. Unlike the membership path there is no query string
 * entry point, so this is not reflected — it is closed together with the reported issue because
 * it is the same unfiltered request-to-markup path (see security report #1318).
 */
class FriendshipButtonRequestOptionsTest extends HumHubDbTestCase
{
    /**
     * Options which must never be taken over from the request.
     */
    public function testUnsafeRequestOptionsAreDiscarded()
    {
        $payloads = [
            '{"friends":{"title":"<img src=x onerror=alert(1)>"}}',
            '{"addFriend":{"attrs":{"data-action-confirm":"<img src=x onerror=alert(1)>"}}}',
            '{"addFriend":{"attrs":{"data-action-url":"https://evil.tld/x"}}}',
            '{"cancelFriendRequest":{"attrs":{"data-action-click":"evil.handler"}}}',
            // Unknown buttons are not configurable at all
            '{"evilButton":{"togglerClass":"btn"}}',
        ];

        foreach ($payloads as $payload) {
            $this->assertSame([], FriendshipButton::sanitizeRequestOptions($payload), $payload);
        }
    }

    /**
     * Malformed or unexpected input must not raise an error.
     */
    public function testInvalidRequestOptionsAreIgnored()
    {
        $this->assertSame([], FriendshipButton::sanitizeRequestOptions('{not json'));
        $this->assertSame([], FriendshipButton::sanitizeRequestOptions('"scalar"'));
        $this->assertSame([], FriendshipButton::sanitizeRequestOptions(''));
        $this->assertSame([], FriendshipButton::sanitizeRequestOptions(null));
        $this->assertSame([], FriendshipButton::sanitizeRequestOptions(['addFriend' => 'not-an-array']));
    }

    /**
     * The presentation options the people directory relies on must still survive the round trip.
     */
    public function testPresentationRequestOptionsArePreserved()
    {
        $this->assertSame(
            [
                'friends' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
                'acceptFriendRequest' => ['togglerClass' => 'btn btn-sm btn-outline-accent'],
            ],
            FriendshipButton::sanitizeRequestOptions(
                '{"friends":{"attrs":{"class":"btn btn-sm btn-outline-accent"}},'
                . '"acceptFriendRequest":{"togglerClass":"btn btn-sm btn-outline-accent"}}',
            ),
        );
    }

    /**
     * A class value must not be usable to break out of the attribute.
     */
    public function testClassValuesCannotInjectAttributes()
    {
        $this->assertSame(
            ['addFriend' => ['attrs' => ['class' => 'btn onmouseoveralert1 x']]],
            FriendshipButton::sanitizeRequestOptions('{"addFriend":{"attrs":{"class":"btn\" onmouseover=alert(1) x=\""}}}'),
        );
    }

    /**
     * End to end: the presentation part still renders, the rest stays server generated.
     */
    public function testPayloadDoesNotReachTheRenderedButton()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        // 'User2' is id 3, so id 2 is a different user and the button is visible.
        $this->becomeUser('User2');

        $options = FriendshipButton::sanitizeRequestOptions(Json::encode([
            'addFriend' => [
                // Legitimate presentation, has to survive
                'attrs' => ['class' => 'btn btn-accent btn-sm'],
                // Must be dropped
                'title' => '<img src=x onerror=alert(1)>',
                'url' => 'javascript:alert(1)',
            ],
        ]));

        $html = FriendshipButton::widget([
            'user' => User::findOne(['id' => 2]),
            'options' => $options,
        ]);

        $this->assertNotSame('', trim($html));
        $this->assertStringContainsString('btn-accent', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }
}

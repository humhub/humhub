<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\MembershipButton;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Json;

/**
 * The membership button is re-rendered after a membership change and its options make a round
 * trip through the client, so request supplied options must be reduced to presentation state
 * before they reach the widget (see security report #1318).
 */
class MembershipButtonRequestOptionsTest extends HumHubDbTestCase
{
    /**
     * Options which must never be taken over from the request, since the rendered button is
     * inserted as markup by the client.
     */
    public function testUnsafeRequestOptionsAreDiscarded()
    {
        $payloads = [
            // Breaks out of the JavaScript string literal in requestMembershipSave.php
            '{"cancelPendingMembership":{"title":"\');alert(1);//"}}',
            // Injects markup into the button content
            '{"cancelPendingMembership":{"title":"<img src=x onerror=alert(1)>"}}',
            // Injects markup which the client renders as confirm dialog body
            '{"cancelPendingMembership":{"attrs":{"data-action-confirm":"<img src=x onerror=alert(1)>"}}}',
            // Retargets the button action
            '{"becomeMember":{"attrs":{"data-action-url":"https://evil.tld/x"}}}',
            '{"cancelMembership":{"url":"javascript:alert(1)"}}',
            // Suppresses the client side action handler
            '{"becomeMember":{"attrs":{"data-action-click":"evil.handler"}}}',
            // Unknown buttons are not configurable at all
            '{"evilButton":{"mode":"link"}}',
        ];

        foreach ($payloads as $payload) {
            $this->assertSame([], MembershipButton::sanitizeRequestOptions($payload), $payload);
        }
    }

    /**
     * Malformed or unexpected input must not raise an error. A malformed value used to surface
     * as an uncaught exception from Json::decode().
     */
    public function testInvalidRequestOptionsAreIgnored()
    {
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('{not json'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('"scalar"'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('42'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(''));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(null));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(0));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(['becomeMember' => 'not-an-array']));
    }

    /**
     * The presentation options the space header and the space directory rely on must still
     * survive the round trip, otherwise the button loses its styling after an AJAX action.
     */
    public function testPresentationRequestOptionsArePreserved()
    {
        // profileHeaderControls.php
        $this->assertSame(
            ['becomeMember' => ['mode' => 'link'], 'acceptInvite' => ['mode' => 'link']],
            MembershipButton::sanitizeRequestOptions('{"becomeMember":{"mode":"link"},"acceptInvite":{"mode":"link"}}'),
        );

        // SpaceDirectoryActionButtons
        $this->assertSame(
            [
                'cancelMembership' => ['visible' => true, 'attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
                'acceptInvite' => ['togglerClass' => 'btn btn-accent btn-sm'],
            ],
            MembershipButton::sanitizeRequestOptions(
                '{"cancelMembership":{"visible":true,"attrs":{"class":"btn btn-sm btn-outline-accent"}},'
                . '"acceptInvite":{"togglerClass":"btn btn-accent btn-sm"}}',
            ),
        );
    }

    /**
     * A class value must not be usable to break out of the attribute and inject further ones.
     */
    public function testClassValuesCannotInjectAttributes()
    {
        $this->assertSame(
            ['becomeMember' => ['attrs' => ['class' => 'btn onmouseoveralert1 x']]],
            MembershipButton::sanitizeRequestOptions('{"becomeMember":{"attrs":{"class":"btn\" onmouseover=alert(1) x=\""}}}'),
        );
    }

    /**
     * Only 'link' mode and the two known methods are accepted.
     */
    public function testModeValuesAreConstrained()
    {
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('{"becomeMember":{"mode":"evil"}}'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('{"becomeMember":{"mode_method":"DELETE"}}'));
        $this->assertSame(
            ['becomeMember' => ['mode_method' => 'GET']],
            MembershipButton::sanitizeRequestOptions('{"becomeMember":{"mode_method":"GET"}}'),
        );
    }

    /**
     * End to end: the presentation part of a mixed payload still renders the button, while the
     * title, the action url and the confirm text stay server generated.
     */
    public function testPayloadDoesNotReachTheRenderedButton()
    {
        // 'User2' (id 3) is a member of Space 1, so the cancelMembership button applies once
        // its presentation option makes it visible.
        $this->becomeUser('User2');

        $payload = "');alert('injected');//";
        $options = MembershipButton::sanitizeRequestOptions(
            Json::encode([
                'cancelMembership' => [
                    // Legitimate presentation, has to survive
                    'visible' => true,
                    'attrs' => ['class' => 'btn btn-sm btn-outline-accent'],
                    // Must all be dropped
                    'title' => $payload,
                    'url' => 'javascript:alert(1)',
                ],
            ]),
        );

        $html = MembershipButton::widget([
            'space' => Space::findOne(['id' => 1]),
            'options' => $options,
        ]);

        // The presentation survived, so the button is actually rendered.
        $this->assertNotSame('', trim($html));
        $this->assertStringContainsString('btn-outline-accent', $html);

        // Nothing attacker controlled reached the markup; the title is the server default.
        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('revoke-membership', $html);
    }
}

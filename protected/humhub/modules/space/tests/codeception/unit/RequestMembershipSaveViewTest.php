<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\MembershipButton;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Json;

/**
 * Ensures request supplied membership button options stay inert when the membership
 * button is re-rendered after a membership request (see security report #1318).
 */
class RequestMembershipSaveViewTest extends HumHubDbTestCase
{
    /**
     * Options which must never be taken over from the request, since the rendered
     * button is inserted as markup by the client.
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
            // Redirects the button action to a foreign target
            '{"becomeMember":{"attrs":{"data-action-url":"https://evil.tld/x"}}}',
            '{"cancelMembership":{"url":"javascript:alert(1)"}}',
            // Unknown buttons are not configurable at all
            '{"evilButton":{"mode":"link"}}',
        ];

        foreach ($payloads as $payload) {
            $this->assertSame([], MembershipButton::sanitizeRequestOptions($payload), $payload);
        }
    }

    /**
     * Malformed or unexpected input must not raise an error.
     */
    public function testInvalidRequestOptionsAreIgnored()
    {
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('{not json'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions('"scalar"'));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(''));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(null));
        $this->assertSame([], MembershipButton::sanitizeRequestOptions(['becomeMember' => 'not-an-array']));
    }

    /**
     * The presentation options used by the space header and the space directory must
     * still survive the round trip through the request.
     */
    public function testPresentationRequestOptionsArePreserved()
    {
        $this->assertSame(
            ['becomeMember' => ['mode' => 'link'], 'acceptInvite' => ['mode' => 'link']],
            MembershipButton::sanitizeRequestOptions('{"becomeMember":{"mode":"link"},"acceptInvite":{"mode":"link"}}'),
        );

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

        // A class value can not be used to inject further attributes
        $this->assertSame(
            ['becomeMember' => ['attrs' => ['class' => 'btn onmouseoveralert1 x']]],
            MembershipButton::sanitizeRequestOptions('{"becomeMember":{"attrs":{"class":"btn\" onmouseover=alert(1) x=\""}}}'),
        );
    }

    /**
     * End to end check of the response which is rendered after a membership request:
     * the payload must neither reach the button markup nor break out of the script.
     */
    public function testPendingMembershipButtonIsRenderedSafely()
    {
        $this->becomeUser('User1');

        // Space 1 uses the application join policy, so requesting membership
        // leaves the current user as an applicant with a pending button.
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        $membership = Membership::findMembership(1, Yii::$app->user->id);
        $this->assertNotNull($membership);
        $this->assertEquals(Membership::STATUS_APPLICANT, $membership->status);

        $payload = "');alert('injected');//";
        $buttonHtml = MembershipButton::widget([
            'space' => $space,
            'options' => MembershipButton::sanitizeRequestOptions(
                Json::encode(['cancelPendingMembership' => ['title' => $payload]]),
            ),
        ]);

        $this->assertStringNotContainsString($payload, $buttonHtml);

        $script = Yii::$app->getView()->renderFile(
            Yii::getAlias('@humhub/modules/space/views/membership/requestMembershipSave.php'),
            ['spaceId' => $space->id, 'newMembershipButton' => $buttonHtml],
        );

        $this->assertStringNotContainsString($payload, $script);
    }

    /**
     * Independent of the sanitized options, the save view must embed the button markup
     * as an encoded JavaScript string literal, so neither a quote nor a closing script
     * tag can terminate the surrounding context.
     */
    public function testSaveViewEncodesButtonMarkupForJavaScript()
    {
        $script = Yii::$app->getView()->renderFile(
            Yii::getAlias('@humhub/modules/space/views/membership/requestMembershipSave.php'),
            ['spaceId' => 1, 'newMembershipButton' => '<a title="\'">x</a></script>'],
        );

        // The markup is escaped, so the raw tags never reach the script context.
        $this->assertStringNotContainsString('<a ', $script);
        $this->assertStringNotContainsString('</a>', $script);
        $this->assertStringContainsString('\\u003C', $script);
        $this->assertStringContainsString('\\u0027', $script);

        // Only the view's own closing tag remains.
        $this->assertSame(1, substr_count($script, '</script>'));
    }
}

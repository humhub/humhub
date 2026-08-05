<?php

namespace tests\codeception\unit\modules\friendship;

use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The friendship button is re-rendered by the server after a friendship change. The client
 * only passes the name of a server side option set back, so a request can never contribute
 * button titles, urls or HTML attributes (see security report #1318).
 */
class FriendshipButtonVariantTest extends HumHubDbTestCase
{
    /**
     * Anything that is not a registered variant name resolves to the default variant.
     */
    public function testUnknownVariantsResolveToDefault()
    {
        $payloads = [
            '{"friends":{"title":"<img src=x onerror=alert(1)>"}}',
            '{"addFriend":{"attrs":{"data-action-url":"https://evil.tld/x"}}}',
            'evilVariant',
            '{not json',
            '',
            null,
            0,
            ['directory'],
        ];

        foreach ($payloads as $payload) {
            $this->assertSame(
                FriendshipButton::VARIANT_DEFAULT,
                FriendshipButton::resolveVariant($payload),
                var_export($payload, true),
            );
            $this->assertSame([], FriendshipButton::getVariantOptions($payload));
        }
    }

    /**
     * The widget normalises the variant itself, so no call site can forget to.
     */
    public function testWidgetNormalisesTheVariant()
    {
        $button = new FriendshipButton(['user' => User::findOne(['id' => 2]), 'variant' => 'evilVariant']);

        $this->assertSame(FriendshipButton::VARIANT_DEFAULT, $button->variant);
    }

    /**
     * The variant is the only thing that travels to the client.
     */
    public function testRenderedButtonExposesOnlyTheVariantName()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        // 'User2' is id 3, so id 2 is a different user and the button is visible.
        $this->becomeUser('User2');

        $html = FriendshipButton::widget([
            'user' => User::findOne(['id' => 2]),
            'variant' => FriendshipButton::VARIANT_DIRECTORY,
        ]);

        $this->assertNotSame('', trim($html));
        $this->assertStringNotContainsString('data-button-options', $html);
        $this->assertStringContainsString('data-button-variant="directory"', $html);
    }

    /**
     * The presentation the people directory used to pass as options must still be reachable.
     */
    public function testDirectoryVariantKeepsTheDirectoryPresentation()
    {
        $options = FriendshipButton::getVariantOptions(FriendshipButton::VARIANT_DIRECTORY);

        $this->assertSame('btn btn-accent btn-sm', $options['addFriend']['attrs']['class']);
        $this->assertSame('btn btn-sm btn-outline-accent', $options['friends']['attrs']['class']);
        $this->assertSame('btn btn-sm btn-outline-accent', $options['acceptFriendRequest']['togglerClass']);
    }

    /**
     * Modules register their own option sets instead of round tripping options.
     */
    public function testRegisteredVariantIsResolvedAndApplied()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);
        // 'User2' is id 3, so id 2 is a different user and the button is visible.
        $this->becomeUser('User2');

        FriendshipButton::registerVariant('test-variant', [
            'addFriend' => ['attrs' => ['class' => 'btn btn-test']],
        ]);

        $this->assertContains('test-variant', FriendshipButton::getVariantNames());

        $options = (new FriendshipButton([
            'user' => User::findOne(['id' => 2]),
            'variant' => 'test-variant',
        ]))->getOptions();

        $this->assertSame('btn btn-test', $options['addFriend']['attrs']['class']);
        // The variant only overrides what it declares.
        $this->assertSame('content.container.relationship', $options['addFriend']['attrs']['data-action-click']);
    }
}

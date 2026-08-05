<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\MembershipButton;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * The membership button is re-rendered by the server after a membership change. The client
 * only passes the name of a server side option set back, so a request can never contribute
 * button titles, urls or HTML attributes (see security report #1318).
 */
class MembershipButtonVariantTest extends HumHubDbTestCase
{
    private function getSpace(): Space
    {
        return Space::findOne(['id' => 1]);
    }

    /**
     * Anything that is not a registered variant name resolves to the default variant, so a
     * request supplied value can neither inject options nor raise an error.
     */
    public function testUnknownVariantsResolveToDefault()
    {
        $payloads = [
            '{"cancelPendingMembership":{"title":"<img src=x onerror=alert(1)>"}}',
            '{"becomeMember":{"attrs":{"data-action-url":"https://evil.tld/x"}}}',
            'evilVariant',
            '{not json',
            '',
            null,
            0,
            ['directory'],
            new \stdClass(),
        ];

        foreach ($payloads as $payload) {
            $this->assertSame(
                MembershipButton::VARIANT_DEFAULT,
                MembershipButton::resolveVariant($payload),
                var_export($payload, true),
            );
            $this->assertSame([], MembershipButton::getVariantOptions($payload));
        }
    }

    /**
     * The widget normalises the variant itself, so no call site can forget to.
     */
    public function testWidgetNormalisesTheVariant()
    {
        $button = new MembershipButton(['space' => $this->getSpace(), 'variant' => 'evilVariant']);

        $this->assertSame(MembershipButton::VARIANT_DEFAULT, $button->variant);
    }

    /**
     * The variant is the only thing that travels to the client.
     */
    public function testRenderedButtonExposesOnlyTheVariantName()
    {
        $this->becomeUser('User2');

        $html = MembershipButton::widget([
            'space' => $this->getSpace(),
            'variant' => MembershipButton::VARIANT_DIRECTORY,
        ]);

        $this->assertStringNotContainsString('data-button-options', $html);
        $this->assertStringContainsString('data-button-variant="directory"', $html);
    }

    /**
     * The presentation the space directory used to pass as options must still be reachable.
     */
    public function testDirectoryVariantKeepsTheDirectoryPresentation()
    {
        $options = MembershipButton::getVariantOptions(MembershipButton::VARIANT_DIRECTORY);

        $this->assertSame('btn btn-accent btn-sm', $options['becomeMember']['attrs']['class']);
        $this->assertSame('btn btn-sm btn-outline-accent', $options['cancelPendingMembership']['attrs']['class']);
        $this->assertTrue($options['cancelMembership']['visible']);
        $this->assertTrue($options['cannotCancelMembership']['visible']);
    }

    /**
     * The space header variant replaces the former inline 'mode' => 'link' options, which turn
     * the action buttons into plain links.
     */
    public function testHeaderVariantRendersLinkMode()
    {
        $this->becomeUser('User2');

        $button = new MembershipButton([
            'space' => $this->getSpace(),
            'variant' => MembershipButton::VARIANT_HEADER,
        ]);
        $options = $button->getOptions();

        $this->assertSame('link', $options['becomeMember']['mode']);
        $this->assertSame('POST', $options['becomeMember']['attrs']['data-method']);
        // Link mode drops the client side action handling entirely.
        $this->assertArrayNotHasKey('data-action-click', $options['becomeMember']['attrs']);
        $this->assertArrayNotHasKey('data-button-variant', $options['becomeMember']['attrs']);
    }

    /**
     * The default variant contributes nothing, so the join url stays free of parameters.
     */
    public function testDefaultVariantIsNotAppendedToTheJoinUrl()
    {
        $this->becomeUser('User2');

        $default = (new MembershipButton(['space' => $this->getSpace()]))->getOptions();
        $directory = (new MembershipButton([
            'space' => $this->getSpace(),
            'variant' => MembershipButton::VARIANT_DIRECTORY,
        ]))->getOptions();

        $this->assertStringNotContainsString('variant', $default['requestMembership']['url']);
        $this->assertStringContainsString('variant=directory', $directory['requestMembership']['url']);
    }

    /**
     * Modules and themes register their own option sets instead of round tripping options.
     */
    public function testRegisteredVariantIsResolvedAndApplied()
    {
        $this->becomeUser('User2');

        MembershipButton::registerVariant('test-variant', [
            'becomeMember' => ['attrs' => ['class' => 'btn btn-test']],
        ]);

        $this->assertContains('test-variant', MembershipButton::getVariantNames());
        $this->assertSame('test-variant', MembershipButton::resolveVariant('test-variant'));

        $options = (new MembershipButton([
            'space' => $this->getSpace(),
            'variant' => 'test-variant',
        ]))->getOptions();

        $this->assertSame('btn btn-test', $options['becomeMember']['attrs']['class']);
        // The variant only overrides what it declares.
        $this->assertSame('content.container.relationship', $options['becomeMember']['attrs']['data-action-click']);
    }

    /**
     * A theme styling the button globally through EVENT_INIT and setDefaultOptions() (as the
     * enterprise theme does) must keep winning over the variant, while explicit widget options
     * keep winning over the theme.
     */
    public function testEventInitDefaultsOverrideTheVariantButNotExplicitOptions()
    {
        $this->becomeUser('User2');

        $handler = function (Event $event) {
            $event->sender->setDefaultOptions([
                'becomeMember' => ['attrs' => ['class' => 'btn btn-theme']],
                'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-theme-pending']],
            ]);
        };
        Event::on(MembershipButton::class, MembershipButton::EVENT_INIT, $handler);

        try {
            $options = (new MembershipButton([
                'space' => $this->getSpace(),
                'variant' => MembershipButton::VARIANT_DIRECTORY,
                'options' => ['becomeMember' => ['attrs' => ['class' => 'btn btn-caller']]],
            ]))->getOptions();
        } finally {
            Event::off(MembershipButton::class, MembershipButton::EVENT_INIT, $handler);
        }

        // Explicit widget options beat the theme.
        $this->assertSame('btn btn-caller', $options['becomeMember']['attrs']['class']);
        // The theme beats the variant.
        $this->assertSame('btn btn-theme-pending', $options['cancelPendingMembership']['attrs']['class']);
        // The variant still applies where the theme is silent.
        $this->assertTrue($options['cancelMembership']['visible']);
    }

    /**
     * Moving the space directory presentation from widget options into a variant reorders it
     * relative to a theme's EVENT_INIT defaults: it used to sit above them, it now sits below.
     * The enterprise theme declares the same class values for every key it shares with the
     * directory, so the reordering must not change the rendered options.
     *
     * @see https://github.com/humhub/enterprise-theme Events::onInitSpaceMembershipButton()
     */
    public function testDirectoryVariantMatchesTheFormerInlineOptionsUnderAThemeHook()
    {
        $this->becomeUser('User2');

        // Verbatim from enterprise-theme Events::onInitSpaceMembershipButton()
        $themeDefaults = [
            'requestMembership' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'becomeMember' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'acceptInvite' => ['attrs' => ['class' => 'btn btn-accent btn-sm'], 'togglerClass' => 'btn btn-accent btn-sm'],
            'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
        ];

        // Verbatim from the options SpaceDirectoryActionButtons passed before the refactor
        $formerInlineOptions = [
            'requestMembership' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'becomeMember' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'acceptInvite' => ['attrs' => ['class' => 'btn btn-accent btn-sm'], 'togglerClass' => 'btn btn-accent btn-sm'],
            'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
            'cancelMembership' => ['visible' => true, 'attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
            'cannotCancelMembership' => ['visible' => true, 'attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
        ];

        $handler = function (Event $event) use ($themeDefaults) {
            $event->sender->setDefaultOptions($themeDefaults);
        };
        Event::on(MembershipButton::class, MembershipButton::EVENT_INIT, $handler);

        try {
            $before = (new MembershipButton([
                'space' => $this->getSpace(),
                'options' => $formerInlineOptions,
            ]))->getOptions();

            $after = (new MembershipButton([
                'space' => $this->getSpace(),
                'variant' => MembershipButton::VARIANT_DIRECTORY,
            ]))->getOptions();
        } finally {
            Event::off(MembershipButton::class, MembershipButton::EVENT_INIT, $handler);
        }

        // Normalise the mechanism itself away: the former join url carried the options JSON
        // where the variant now names the option set, and the variant marker is the attribute
        // that replaced data-button-options. Everything else must be untouched.
        $normalise = static function (array $options): array {
            unset($options['requestMembership']['url']);

            foreach ($options as $button => $config) {
                unset($options[$button]['attrs']['data-button-variant']);
            }

            return $options;
        };

        $this->assertSame($normalise($before), $normalise($after));
    }

    /**
     * The request membership form carries the variant through the modal, so a submitted
     * value has to be loadable but must always end up as a registered variant.
     */
    public function testRequestMembershipFormFiltersTheSubmittedVariant()
    {
        $model = new RequestMembershipForm();

        $this->assertTrue($model->load([
            'RequestMembershipForm' => [
                'message' => 'Let me in!',
                'variant' => MembershipButton::VARIANT_DIRECTORY,
            ],
        ]));
        $this->assertTrue($model->validate());
        $this->assertSame(MembershipButton::VARIANT_DIRECTORY, $model->variant);

        $hostile = new RequestMembershipForm();
        $hostile->load([
            'RequestMembershipForm' => [
                'message' => 'Let me in!',
                'variant' => '{"cancelPendingMembership":{"title":"<img src=x onerror=alert(1)>"}}',
            ],
        ]);

        $this->assertTrue($hostile->validate());
        $this->assertSame(MembershipButton::VARIANT_DEFAULT, $hostile->variant);
    }

    /**
     * The form no longer carries a serialized option array.
     */
    public function testRequestMembershipFormHasNoOptionsAttribute()
    {
        $this->assertNotContains('options', (new RequestMembershipForm())->attributes());
    }
}

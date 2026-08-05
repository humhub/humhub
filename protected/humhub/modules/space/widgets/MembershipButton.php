<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * MembershipButton shows various membership related buttons in space header.
 *
 * @author luke
 * @since 0.11
 */
class MembershipButton extends Widget
{
    /**
     * Presentation variant used by the space header.
     */
    public const VARIANT_HEADER = 'header';

    /**
     * Presentation variant used by the space directory.
     */
    public const VARIANT_DIRECTORY = 'directory';

    /**
     * Presentation variant used when no variant is given.
     */
    public const VARIANT_DEFAULT = 'default';

    /**
     * Option sets which may be selected by name. Since the button is re-rendered after
     * a membership change, the client only passes the variant name back to the server,
     * which keeps the actual option values under server control.
     *
     * @var array<string, array>
     * @see registerVariant()
     */
    private static $variants = [
        self::VARIANT_DEFAULT => [],
        self::VARIANT_HEADER => [
            'becomeMember' => ['mode' => 'link'],
            'acceptInvite' => ['mode' => 'link'],
        ],
        self::VARIANT_DIRECTORY => [
            'requestMembership' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'becomeMember' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'acceptInvite' => ['attrs' => ['class' => 'btn btn-accent btn-sm'], 'togglerClass' => 'btn btn-accent btn-sm'],
            'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
            'cancelMembership' => ['visible' => true, 'attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
            'cannotCancelMembership' => ['visible' => true, 'attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
        ],
    ];

    /**
     * @var Space
     */
    public $space;

    /**
     * @var string Name of the presentation variant, see [[registerVariant()]]. Unknown
     * names fall back to [[VARIANT_DEFAULT]].
     * @since 1.19
     */
    public $variant = self::VARIANT_DEFAULT;

    /**
     * @var array Options buttons
     */
    public $options = [];

    /**
     * Registers an option set which can be selected by name through [[$variant]].
     *
     * Use this instead of passing options which have to survive the re-render after a
     * membership change, since options are no longer round tripped through the client.
     *
     * @since 1.19
     */
    public static function registerVariant(string $name, array $options): void
    {
        self::$variants[$name] = $options;
    }

    /**
     * @return string[] names of all registered variants
     * @since 1.19
     */
    public static function getVariantNames(): array
    {
        return array_keys(self::$variants);
    }

    /**
     * Maps a variant name to a registered variant. Unknown or malformed values, e.g. when
     * supplied by a request, resolve to [[VARIANT_DEFAULT]].
     *
     * @param mixed $variant
     * @since 1.19
     */
    public static function resolveVariant($variant): string
    {
        return is_string($variant) && isset(self::$variants[$variant]) ? $variant : self::VARIANT_DEFAULT;
    }

    /**
     * @return array the options of the given variant
     * @since 1.19
     */
    public static function getVariantOptions($variant): array
    {
        return self::$variants[static::resolveVariant($variant)];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->variant = static::resolveVariant($this->variant);

        parent::init();
    }

    private function getDefaultOptions()
    {
        return [
            'requestMembership' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'url' => $this->space->createUrl('/space/membership/request-membership-form', $this->variant === self::VARIANT_DEFAULT ? [] : ['variant' => $this->variant]),
                'attrs' => [
                    'class' => 'btn btn-accent',
                    'data-space-request-membership' => $this->space->id,
                    'data-bs-target' => '#globalModal',
                ],
            ],
            'becomeMember' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/request-membership'),
                    'data-button-variant' => $this->variant,
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent',
                    'data-space-request-membership' => $this->space->id,
                ],
            ],
            'acceptInvite' => [
                'title' => Yii::t('SpaceModule.base', 'Accept Invite'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/invite-accept'),
                    'data-button-variant' => $this->variant,
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent',
                ],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-accent',
            ],
            'declineInvite' => [
                'title' => Yii::t('SpaceModule.base', 'Decline Invite'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-button-variant' => $this->variant,
                    'data-ui-loader' => '',
                ],
            ],
            'cancelPendingMembership' => [
                'title' => Icon::get('clock-o') . Yii::t('SpaceModule.base', 'Pending'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to withdraw your request to join Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
                    'data-button-variant' => $this->variant,
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent active',
                ],
            ],
            'cancelMembership' => [
                'visible' => false,
                'title' => Icon::get('check') . Yii::t('SpaceModule.base', 'Member'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-action-confirm-header' => Yii::t('SpaceModule.base', '<strong>Leave</strong> Space'),
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to end your membership in Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
                    'data-action-confirm-text' => Yii::t('SpaceModule.base', 'Leave'),
                    'data-button-variant' => $this->variant,
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent active',
                ],
            ],
            'cannotCancelMembership' => [
                'visible' => false,
                'memberTitle' => Icon::get('check') . Yii::t('SpaceModule.base', 'Member'),
                'ownerTitle' => Icon::get('user') . Yii::t('SpaceModule.base', 'Owner'),
                'attrs' => ['class' => 'btn btn-accent active'],
            ],
        ];
    }

    public function setDefaultOptions(array $defaultOptions)
    {
        $this->options = $this->getOptions($defaultOptions);
    }

    public function getOptions(?array $defaultOptions = null): array
    {
        if ($defaultOptions === null) {
            // Effective precedence: defaults < variant < setDefaultOptions() < $this->options
            $defaultOptions = ArrayHelper::merge(
                $this->getDefaultOptions(),
                static::getVariantOptions($this->variant),
            );
        }

        return $this->prepareButtonOptions(ArrayHelper::merge($defaultOptions, $this->options));
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->space->isBlockedForUser()) {
            return '';
        }

        return $this->render('membershipButton', [
            'space' => $this->space,
            'membership' => $this->space->getMembership(),
            'options' => $this->getOptions(),
            'canCancelMembership' => !$this->space->isSpaceOwner() && $this->space->canLeave(),
        ]);
    }

    private function prepareButtonOptions(array $options): array
    {
        foreach ($options as $b => $button) {
            if (isset($button['mode']) && $button['mode'] === 'link' && isset($button['attrs']['data-action-url'])) {
                // Switch button to link mode
                $button['url'] = $button['attrs']['data-action-url'];
                $button['attrs']['data-method'] = $button['mode_method'] ?? 'POST';
                unset($button['attrs']['data-action-click']);
                unset($button['attrs']['data-action-url']);
                unset($button['attrs']['data-button-variant']);
                $options[$b] = $button;
            }
        }

        return $options;
    }

}

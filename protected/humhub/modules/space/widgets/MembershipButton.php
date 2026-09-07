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
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * MembershipButton shows various membership related buttons in space header.
 *
 * @author luke
 * @since 0.11
 */
class MembershipButton extends Widget
{
    /**
     * Buttons which may be addressed by request supplied options.
     */
    private const REQUEST_BUTTONS = [
        'requestMembership',
        'becomeMember',
        'acceptInvite',
        'declineInvite',
        'cancelPendingMembership',
        'cancelMembership',
        'cannotCancelMembership',
    ];

    /**
     * Presentation only options which may be supplied by the request. Everything else —
     * titles, urls and all `data-action-*` attributes — is server generated and must not be
     * overridable, since the rendered button is inserted as markup by the client.
     */
    private const REQUEST_OPTIONS = ['mode', 'mode_method', 'visible', 'groupClass', 'togglerClass'];

    /**
     * @var Space
     */
    public $space;

    /**
     * @var array Options buttons
     */
    public $options = [];

    /**
     * Reduces request supplied button options to a set of harmless presentation options.
     *
     * The button is re-rendered after a membership change and has to keep the presentation of
     * the context it was rendered in, so the options make a round trip through the client.
     * Only the presentation state that actually needs to survive that round trip is accepted.
     *
     * @param mixed $options JSON encoded or already decoded button options
     * @return array the sanitized options
     * @since 1.18.5
     */
    public static function sanitizeRequestOptions($options): array
    {
        if (is_string($options)) {
            try {
                $options = Json::decode($options);
            } catch (InvalidArgumentException $e) {
                return [];
            }
        }

        if (!is_array($options)) {
            return [];
        }

        $sanitized = [];

        foreach ($options as $button => $config) {
            if (!in_array($button, self::REQUEST_BUTTONS, true) || !is_array($config)) {
                continue;
            }

            foreach ($config as $option => $value) {
                if (!in_array($option, self::REQUEST_OPTIONS, true)) {
                    continue;
                }

                switch ($option) {
                    case 'mode':
                        if ($value === 'link') {
                            $sanitized[$button][$option] = $value;
                        }
                        break;
                    case 'mode_method':
                        if (in_array($value, ['GET', 'POST'], true)) {
                            $sanitized[$button][$option] = $value;
                        }
                        break;
                    case 'visible':
                        $sanitized[$button][$option] = (bool)$value;
                        break;
                    default:
                        $sanitized[$button][$option] = static::sanitizeCssClass($value);
                        break;
                }
            }

            if (isset($config['attrs']['class'])) {
                $sanitized[$button]['attrs']['class'] = static::sanitizeCssClass($config['attrs']['class']);
            }
        }

        return $sanitized;
    }

    private static function sanitizeCssClass($value): string
    {
        return is_string($value) ? preg_replace('/[^\w \-]/', '', $value) : '';
    }

    private function getDefaultOptions()
    {
        return [
            'requestMembership' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'url' => $this->space->createUrl('/space/membership/request-membership-form', empty($this->options) ? [] : ['options' => Json::encode($this->options)]),
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
                    'data-button-options' => Json::encode($this->options),
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
                    'data-button-options' => Json::encode($this->options),
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
                    'data-button-options' => Json::encode($this->options),
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
                    'data-button-options' => Json::encode($this->options),
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
                    'data-button-options' => Json::encode($this->options),
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
            $defaultOptions = $this->getDefaultOptions();
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
                unset($button['attrs']['data-button-options']);
                $options[$b] = $button;
            }
        }

        return $options;
    }

}

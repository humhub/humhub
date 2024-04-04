<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Space;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
     * @var Space
     */
    public $space;

    /**
     * @var array Options buttons
     */
    public $options = [];

    private function getDefaultOptions()
    {
        return [
            'requestMembership' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'url' => $this->space->createUrl('/space/membership/request-membership-form', empty($this->options) ? [] : ['options' => Json::encode($this->options)]),
                'attrs' => [
                    'class' => 'btn btn-info',
                    'data-space-request-membership' => $this->space->id,
                    'data-target' => '#globalModal',
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
                    'class' => 'btn btn-info',
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
                    'class' => 'btn btn-info',
                ],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-info',
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
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Pending'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to withdraw your request to join Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info active',
                ],
            ],
            'cancelMembership' => [
                'visible' => false,
                'title' => '<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Member'),
                'url' => '#',
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-action-confirm-header' => Yii::t('SpaceModule.base', '<strong>Leave</strong> Space'),
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to end your membership in Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
                    'data-action-confirm-text' => Yii::t('SpaceModule.base', 'Leave'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info active',
                ],
            ],
            'cannotCancelMembership' => [
                'visible' => false,
                'memberTitle' => '<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Member'),
                'ownerTitle' => '<span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Owner'),
                'attrs' => ['class' => 'btn btn-info active'],
            ],
        ];
    }

    public function setDefaultOptions(array $defaultOptions)
    {
        $this->options = $this->getOptions($defaultOptions);
    }

    public function getOptions(array $defaultOptions = null): array
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

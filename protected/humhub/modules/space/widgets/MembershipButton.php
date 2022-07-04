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
                'url' => $this->space->createUrl('/space/membership/request-membership-form', ['options' => Json::encode($this->options)]),
                'attrs' => [
                    'class' => 'btn btn-info',
                    'data-space-request-membership' => $this->space->id,
                    'data-target' => '#globalModal',
                ],
            ],
            'becomeMember' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'mode' => 'ajax', // 'ajax' - to use data-action-* options for AJAX request, 'link' - to use button as simple <a> link
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
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                ],
            ],
            'cancelPendingMembership' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Pending'),
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
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'data-action-confirm' => Yii::t('SpaceModule.base', 'Would you like to end your membership in Space {spaceName}?', ['{spaceName}' => '<strong>' . Html::encode($this->space->getDisplayName()) . '</strong>']),
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

        return ArrayHelper::merge($defaultOptions, $this->options);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->space->isBlockedForUser()) {
            return '';
        }

        $options = $this->getOptions();

        if ($options['becomeMember']['mode'] == 'link') {
            // Switch button "Join" to link mode
            $options['becomeMember']['url'] = $options['becomeMember']['attrs']['data-action-url'];
            $options['becomeMember']['attrs']['data-method'] = 'POST';
            unset($options['becomeMember']['attrs']['data-action-click']);
            unset($options['becomeMember']['attrs']['data-action-url']);
        }

        return $this->render('membershipButton', [
            'space' => $this->space,
            'membership' => $this->space->getMembership(),
            'options' => $options,
            'canCancelMembership' => !$this->space->isSpaceOwner() && $this->space->canLeave(),
        ]);
    }

}

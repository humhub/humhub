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
                'attrs' => ['id' => 'requestMembershipButton', 'class' => 'btn btn-info', 'data-target' => '#globalModal'],
            ],
            'becomeMember' => [
                'title' => Yii::t('SpaceModule.base', 'Join'),
                'attrs' => ['id' => 'requestMembershipButton', 'class' => 'btn btn-info', 'data-method' => 'POST'],
            ],
            'acceptInvite' => [
                'title' => Yii::t('SpaceModule.base', 'Accept Invite'),
                'attrs' => ['class' => 'btn btn-info', 'data-method' => 'POST'],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-info',
            ],
            'declineInvite' => [
                'title' => Yii::t('SpaceModule.base', 'Decline Invite'),
                'attrs' => ['data-method' => 'POST'],
            ],
            'cancelPendingMembership' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Pending'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('SpaceModule.base', 'Would you like to withdraw your request to join Space {spaceName}?', ['{spaceName}' => '"' . $this->space->getDisplayName() . '"']),
                    'class' => 'btn btn-info active',
                ],
            ],
            'member' => [
                'title' => '<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('SpaceModule.base', 'Member'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('SpaceModule.base', 'Would you like to end your membership in Space {spaceName}?', ['{spaceName}' => '"' . $this->space->getDisplayName() . '"']),
                    'class' => 'btn btn-info active',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('membershipButton', [
            'space' => $this->space,
            'membership' => $this->space->getMembership(),
            'options' => ArrayHelper::merge($this->getDefaultOptions(), $this->options),
        ]);
    }

}

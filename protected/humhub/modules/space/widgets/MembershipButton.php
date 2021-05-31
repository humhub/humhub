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
                'title' => Yii::t('SpaceModule.base', 'Request membership'),
                'attrs' => ['id' => 'requestMembershipButton', 'class' => 'btn btn-primary', 'data-target' => '#globalModal'],
            ],
            'becomeMember' => [
                'title' => Yii::t('SpaceModule.base', 'Become member'),
                'attrs' => ['id' => 'requestMembershipButton', 'class' => 'btn btn-primary', 'data-method' => 'POST'],
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
                'title' => Yii::t('SpaceModule.base', 'Cancel pending membership application'),
                'attrs' => ['data-method' => 'POST', 'class' => 'btn btn-primary'],
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

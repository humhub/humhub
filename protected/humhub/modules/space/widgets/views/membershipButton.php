<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use yii\helpers\Html;

if ($membership === null) {
    if ($space->canJoin()) {
        if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
            echo Html::a(Yii::t('SpaceModule.widgets_views_membershipButton', 'Request membership'), $space->createUrl('/space/membership/request-membership-form'), array('id' => 'requestMembershipButton', 'class' => 'btn btn-primary', 'data-target' => '#globalModal'));
        } else {
            echo Html::a(Yii::t('SpaceModule.widgets_views_membershipButton', 'Become member'), $space->createUrl('/space/membership/request-membership'), array('id' => 'requestMembershipButton', 'class' => 'btn btn-primary', 'data-method' => 'POST'));
        }
    }
} elseif ($membership->status == Membership::STATUS_INVITED) {
    ?>
    <div class="btn-group">
        <?php echo Html::a(Yii::t('SpaceModule.widgets_views_membershipButton', 'Accept Invite'), $space->createUrl('/space/membership/invite-accept'), array('class' => 'btn btn-info', 'data-method' => 'POST')); ?>
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><?php echo Html::a(Yii::t('SpaceModule.widgets_views_membershipButton', 'Deny Invite'), $space->createUrl('/space/membership/revoke-membership'), array('data-method' => 'POST')); ?></li>
        </ul>
    </div>
    <?php
} elseif ($membership->status == Membership::STATUS_APPLICANT) {
    echo Html::a(Yii::t('SpaceModule.widgets_views_membershipButton', 'Cancel pending membership application'), $space->createUrl('/space/membership/revoke-membership'), array('data-method' => 'POST', 'class' => 'btn btn-primary'));
}
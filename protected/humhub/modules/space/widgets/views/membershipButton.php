<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use yii\helpers\Html;

/* @var $membership Membership */
/* @var $space Space */
/* @var $options array */
/* @var $canCancelMembership bool */

if ($membership === null) {
    if ($space->canJoin()) {
        if ($space->join_policy == Space::JOIN_POLICY_APPLICATION) {
            echo Html::a($options['requestMembership']['title'], $options['requestMembership']['url'], $options['requestMembership']['attrs']);
        } else {
            echo Html::a($options['becomeMember']['title'], $options['becomeMember']['url'], $options['becomeMember']['attrs']);
        }
    }
} elseif ($membership->status == Membership::STATUS_INVITED) {
    ?>
    <div class="<?= $options['acceptInvite']['groupClass'] ?>">
        <?= Html::a($options['acceptInvite']['title'], '#', $options['acceptInvite']['attrs']); ?>
        <button type="button" class="<?= $options['acceptInvite']['togglerClass'] ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a($options['declineInvite']['title'], '#', $options['declineInvite']['attrs']); ?></li>
        </ul>
    </div>
    <?php
} elseif ($membership->status == Membership::STATUS_APPLICANT) {
    echo Html::a($options['cancelPendingMembership']['title'], $space->createUrl('/space/membership/revoke-membership'), $options['cancelPendingMembership']['attrs']);
} elseif ($membership->status == Membership::STATUS_MEMBER) {
    if ($canCancelMembership && $options['cancelMembership']['visible']) {
        echo Html::a($options['cancelMembership']['title'], '#', $options['cancelMembership']['attrs']);
    } elseif (!$canCancelMembership && $options['cannotCancelMembership']['visible']) {
        $memberTitle = ($space->isSpaceOwner() ? $options['cannotCancelMembership']['ownerTitle'] : $options['cannotCancelMembership']['memberTitle']);
        echo Html::a($memberTitle, $space->createUrl(), $options['cannotCancelMembership']['attrs']);
    }
}

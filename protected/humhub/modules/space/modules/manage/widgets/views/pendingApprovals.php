<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\Image;
use humhub\widgets\bootstrap\Link;

/* @var $applicants Membership[] */
/* @var $space Space */
?>

<div class="panel panel-danger">
    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>New</strong> member request'); ?></div>
    <div class="panel-body">
        <div class="d-flex flex-column gap-3">
            <?php foreach ($applicants as $membership) : ?>
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        <?= Image::widget(['user' => $membership->user]); ?>
                    </div>
                    <div class="flex-grow-1">
                        <strong><?= Html::encode($membership->user->displayName); ?></strong><br>
                        <?= Html::encode($membership->user->displayNameSub); ?><br>
                        <i><small><?= Html::encode($membership->request_message); ?></small></i><br>

                        <hr>
                        <?= Link::success(Yii::t('SpaceModule.base', 'Accept'))->post($space->createUrl('/space/manage/member/approve-applicant', ['userGuid' => $membership->user->guid]))->sm() ?>
                        <?= Link::danger(Yii::t('SpaceModule.base', 'Decline'))->post($space->createUrl('/space/manage/member/reject-applicant', ['userGuid' => $membership->user->guid]))->sm() ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

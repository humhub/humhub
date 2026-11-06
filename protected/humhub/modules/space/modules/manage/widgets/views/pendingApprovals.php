<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\Image;
use humhub\widgets\bootstrap\Link;

/* @var $applicants Membership[] */
/* @var $space Space */

$buttonStyle = '--bs-btn-padding-y: .125rem; --bs-btn-padding-x: .4rem; --bs-btn-font-size: .75rem';
?>

<div class="panel panel-danger">
    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>New</strong> member request'); ?></div>
    <div class="panel-body">
        <div class="d-flex flex-column gap-3">
            <?php foreach ($applicants as $membership) : ?>
                <div class="d-flex">
                    <div class="flex-shrink-0 me-2">
                        <?= Image::widget(['user' => $membership->user, 'width' => 32]); ?>
                    </div>
                    <div class="flex-grow-1 text-break">
                        <strong><?= Html::encode($membership->user->displayName); ?></strong>
                        <?php if ($membership->user->displayNameSub) : ?>
                            <div class="small"><?= Html::encode($membership->user->displayNameSub); ?></div>
                        <?php endif; ?>
                        <?php if ($membership->request_message) : ?>
                            <div class="small fst-italic"><?= Html::encode($membership->request_message); ?></div>
                        <?php endif; ?>
                        <div class="mt-1">
                            <?= Link::success(Yii::t('SpaceModule.base', 'Accept'))->post($space->createUrl('/space/manage/member/approve-applicant', ['userGuid' => $membership->user->guid]))->sm()->style($buttonStyle) ?>
                            <?= Link::danger(Yii::t('SpaceModule.base', 'Decline'))->post($space->createUrl('/space/manage/member/reject-applicant', ['userGuid' => $membership->user->guid]))->sm()->style($buttonStyle) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php

use humhub\modules\user\widgets\Image;
use yii\helpers\Html;
use humhub\widgets\Link;

/* @var $applicants \humhub\modules\space\models\Membership[] */
/* @var $space \humhub\modules\space\models\Space */
?>

<div class="panel panel-danger">
    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>New</strong> member request'); ?></div>
    <div class="panel-body">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <?php foreach ($applicants as $membership) : ?>
                <tr>
                    <td style="padding-right:12px;vertical-align: top">
                        <?= Image::widget(['user' => $membership->user]); ?>
                    </td>
                    <td style="vertical-align: top">
                        <strong><?= Html::encode($membership->user->displayName); ?></strong><br>
                        <?= Html::encode($membership->user->displayNameSub); ?><br>
                        <i><small><?= Html::encode($membership->request_message); ?></small></i><br>

                        <hr>
                        <?= Link::success(Yii::t('SpaceModule.base', 'Accept'))->post($space->createUrl('/space/manage/member/approve-applicant', ['userGuid' => $membership->user->guid]))->sm() ?>
                        <?= Link::danger(Yii::t('SpaceModule.base', 'Decline'))->post($space->createUrl('/space/manage/member/reject-applicant', ['userGuid' => $membership->user->guid]))->sm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

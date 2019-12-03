<?php

use yii\helpers\Html;
use humhub\widgets\Link;
?>

<div class="panel panel-danger">
    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>New</strong> member request'); ?></div>
    <div class="panel-body">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <?php foreach ($applicants as $membership) : ?>
                <?php $user = $membership->user; ?>
                <tr>
                    <td align="left" valign="top" width="30">
                        <a href="<?= $user->getUrl(); ?>" alt="<?= Html::encode($user->displayName); ?>">
                            <img class="img-rounded tt img_margin"
                                 src="<?= $user->getProfileImage()->getUrl(); ?>" height="24" width="24"
                                 alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;"
                                 data-toggle="tooltip" data-placement="top" title=""
                                 data-original-title="<?= Html::encode($user->displayName); ?>"/>
                        </a>
                    </td>

                    <td align="left" valign="top">
                        <strong><?= Html::encode($user->displayName); ?></strong><br>
                        <?= Html::encode($membership->request_message); ?><br>

                        <hr>
                        <?= Link::success(Yii::t('SpaceModule.base', 'Accept'))->post($space->createUrl('/space/manage/member/approve-applicant', ['userGuid' => $user->guid]))->sm() ?>
                        <?= Link::danger(Yii::t('SpaceModule.base', 'Decline'))->post($space->createUrl('/space/manage/member/reject-applicant', ['userGuid' => $user->guid]))->sm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

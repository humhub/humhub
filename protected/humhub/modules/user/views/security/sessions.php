<?php

use humhub\helpers\Html;
use humhub\modules\user\models\UserSession;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\Icon;
use humhub\widgets\TimeAgo;

/* @var UserSession[] $sessions */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<p class="text-body-secondary">
    <?= Yii::t('UserModule.account', 'These devices are currently logged in to your account. Log out any session you do not recognize.') ?>
</p>

<ul class="list-group mb-3">
    <?php foreach ($sessions as $session): ?>
        <li class="list-group-item d-flex align-items-center gap-3" data-session-key="<?= Html::encode($session->key) ?>">
            <?= Icon::get($session->isMobile ? 'device-mobile' : 'device-desktop')->size(Icon::SIZE_LG) ?>
            <div class="flex-grow-1">
                <strong title="<?= Html::encode((string)$session->userAgent) ?>"><?= Html::encode($session->device) ?></strong>
                <div class="text-body-secondary small">
                    <?php if ($session->loggedInAt !== null): ?>
                        <?= Yii::t('UserModule.account', 'Logged in {date}', ['date' => Yii::$app->formatter->asDatetime($session->loggedInAt, 'short')]) ?> ·
                    <?php endif ?>
                    <?= Yii::t('UserModule.account', 'Last active {time}', ['time' => TimeAgo::widget(['timestamp' => $session->lastActivity])]) ?>
                </div>
            </div>
            <?php if ($session->isCurrent): ?>
                <span class="badge text-bg-success"><?= Yii::t('UserModule.account', 'This session') ?></span>
            <?php else: ?>
                <?= Link::danger(Yii::t('UserModule.account', 'Log out'))
                    ->post(['/user/security/terminate-session', 'key' => $session->key])
                    ->confirm(
                        Yii::t('UserModule.account', 'Log out this session?'),
                        Html::encode(Yii::t('UserModule.account', 'Devices with “Remember me” enabled can sign in again. Use “Log out all other sessions” to sign them out for good.')),
                    )
                    ->sm() ?>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<?php if (count($sessions) > 1): ?>
    <?= Link::danger(Yii::t('UserModule.account', 'Log out all other sessions'))
        ->post(['/user/security/terminate-other-sessions'])
        ->confirm(
            Yii::t('UserModule.account', 'Log out all other sessions?'),
            Yii::t('UserModule.account', 'You will stay logged in on this device only.'),
        )
        ->sm() ?>
<?php endif ?>
<?php $this->endContent() ?>

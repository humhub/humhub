<?php

use humhub\helpers\Html;
use humhub\modules\user\widgets\Image;
use humhub\widgets\AjaxLinkPager;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $users \humhub\modules\user\models\User[] */
/* @var $hideOnlineStatus bool */
/* @var $title string */
?>

<?php Modal::beginDialog([
    'title' => $title,
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

<?php if (count($users) === 0): ?>
    <p><?= Yii::t('UserModule.base', 'No users found.'); ?></p>
<?php endif; ?>

<div id="userlist-content">

    <div class="media-list">
        <?php foreach ($users as $user) : ?>
        <a href="<?= $user->getUrl(); ?>" data-modal-close="1" class="d-flex">
            <div class="flex-shrink-0">
                <?= Image::widget([
                    'user' => $user,
                    'link' => false,
                    'hideOnlineStatus' => $hideOnlineStatus,
                ]) ?>
            </div>

            <div class="flex-grow-1">
                <h4 class="mt-0"><?= Html::encode($user->displayName); ?></h4>
                <h5><?= Html::encode($user->displayNameSub); ?></h5>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="pagination-container">
    <?= AjaxLinkPager::widget(['pagination' => $pagination]); ?>
</div>

<script <?= Html::nonce() ?>>

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>

<?php Modal::endDialog() ?>

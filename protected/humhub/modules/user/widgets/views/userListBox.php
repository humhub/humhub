<?php

use humhub\helpers\Html;
use humhub\modules\user\widgets\Image;
use humhub\widgets\AjaxLinkPager;
use humhub\widgets\modal\Modal;
use yii\data\Pagination;

/* @var $users \humhub\modules\user\models\User[] */
/* @var $hideOnlineStatus bool */
/* @var $title string */
/* @var $pagination Pagination */
?>

<?php Modal::beginDialog([
    'title' => $title,
    'footer' => Html::tag('div', AjaxLinkPager::widget(['pagination' => $pagination]), ['class' => 'pagination-container']),
]) ?>

    <?php if (count($users) === 0): ?>
        <p><?= Yii::t('UserModule.base', 'No users found.') ?></p>
    <?php endif; ?>

    <div id="userlist-content" class="hh-list">
        <?php foreach ($users as $user) : ?>
            <a href="<?= $user->getUrl() ?>" data-modal-close="1" class="d-flex">
                <div class="flex-shrink-0 me-2">
                    <?= Image::widget([
                        'user' => $user,
                        'link' => false,
                        'hideOnlineStatus' => $hideOnlineStatus,
                    ]) ?>
                </div>

                <div class="flex-grow-1">
                    <h4 class="mt-0"><?= Html::encode($user->displayName) ?></h4>
                    <h5><?= Html::encode($user->displayNameSub) ?></h5>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <script <?= Html::nonce() ?>>
        // scroll to top of list
        $(".modal-body").animate({scrollTop: 0}, 200);
    </script>

<?php Modal::endDialog() ?>

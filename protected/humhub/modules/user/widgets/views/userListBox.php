<?php

use humhub\modules\user\widgets\Image;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use humhub\widgets\AjaxLinkPager;
use yii\helpers\Html;

/* @var $users \humhub\modules\user\models\User[] */
?>

<?php ModalDialog::begin(['header' => $title]) ?>

    <?php if (count($users) === 0): ?>
        <div class="modal-body">
            <p><?= Yii::t('UserModule.base', 'No users found.'); ?></p>
        </div>
    <?php endif; ?>

    <div id="userlist-content">

        <ul class="media-list">
            <?php foreach ($users as $user) : ?>
                <li>
                    <a href="<?= $user->getUrl(); ?>" data-modal-close="1">
                        <div class="media">
                            <?= Image::widget([
                                'user' => $user,
                                'link' => false,
                                'htmlOptions' => ['class' => 'media-object pull-left'],
                            ]) ?>

                            <div class="media-body">
                                <h4 class="media-heading"><?= Html::encode($user->displayName); ?></h4>
                                <h5><?= Html::encode($user->displayNameSub); ?></h5>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="pagination-container">
            <?= AjaxLinkPager::widget(['pagination' => $pagination]); ?>
        </div>
    </div>

    <div class="modal-footer">
        <?= ModalButton::cancel(Yii::t('base', 'Close'))?>
    </div>

<script <?= \humhub\libs\Html::nonce() ?>>

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>

<?php ModalDialog::end() ?>



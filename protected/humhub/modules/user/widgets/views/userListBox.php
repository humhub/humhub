<?php

use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\helpers\Html;

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
                    <a href="<?= $user->getUrl(); ?>">
                        <div class="media">
                            <img class="media-object img-rounded pull-left"
                                 src="<?= $user->getProfileImage()->getUrl(); ?>" width="50"
                                 height="50" alt="50x50" data-src="holder.js/50x50"
                                 style="width: 50px; height: 50px;">

                            <div class="media-body">
                                <h4 class="media-heading"><?= Html::encode($user->displayName); ?></h4>
                                <h5><?= Html::encode($user->profile->title); ?></h5>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="pagination-container">
            <?= \humhub\widgets\AjaxLinkPager::widget(['pagination' => $pagination]); ?>
        </div>
    </div>

    <div class="modal-footer">
        <?= ModalButton::cancel(Yii::t('base', 'Close'))?>
    </div>

<?php ModalDialog::end() ?>

<script type="text/javascript">

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>


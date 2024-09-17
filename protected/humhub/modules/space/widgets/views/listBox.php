<?php

use humhub\modules\space\models\Space;
use humhub\widgets\AjaxLinkPager;
use humhub\widgets\bootstrap\Html;
use humhub\widgets\ModalDialog;

/* @var $spaces Space[] */
?>


<?php ModalDialog::begin(['header' => $title]) ?>

<?php if (count($spaces) === 0) : ?>
    <div class="modal-body">
        <p><?= Yii::t('SpaceModule.base', 'No spaces found.'); ?></p>
    </div>
<?php endif; ?>

<div id="spacelist-content">

    <div class="media-list">
        <!-- BEGIN: Results -->
        <?php foreach ($spaces as $space) : ?>
            <a href="<?= $space->getUrl() ?>" data-modal-close="1" class="d-flex">
                <div class="flex-shrink-0">
                    <img class="rounded"
                     src="<?= $space->getProfileImage()->getUrl() ?>" width="50"
                     height="50" style="width: 50px; height: 50px;">
                </div>

                <div class="flex-grow-1">
                    <h4 class="mt-0"><?= Html::encode($space->name) ?></h4>
                    <h5><?= Html::encode($space->description) ?></h5>
                </div>
            </a>

        <?php endforeach; ?>
        <!-- END: Results -->
    </div>

    <div class="pagination-container">
        <?= AjaxLinkPager::widget(['pagination' => $pagination]); ?>
    </div>

</div>

<script <?= Html::nonce() ?>>
    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);
</script>

<?php ModalDialog::end() ?>

<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\AjaxLinkPager;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use humhub\libs\Html;

/* @var $spaces Space[] */
?>


<?php ModalDialog::begin(['header' => $title]) ?>

<?php if (count($spaces) === 0) : ?>
    <div class="modal-body">
        <p><?= Yii::t('SpaceModule.base', 'No spaces found.'); ?></p>
    </div>
<?php endif; ?>

<div id="spacelist-content">

    <ul class="media-list">
        <!-- BEGIN: Results -->
        <?php foreach ($spaces as $space) : ?>
            <li>
                <a href="<?= $space->getUrl(); ?>" data-modal-close="1">

                    <div class="media">
                        <?= Image::widget([
                            'space' => $space,
                            'width' => 50,
                            'htmlOptions' => ['class' => 'media-object pull-left'],
                        ]) ?>

                        <div class="media-body">
                            <h4 class="media-heading"><?= Html::encode($space->name); ?></h4>
                            <h5><?= Html::encode($space->description); ?></h5>
                        </div>
                    </div>
                </a>
            </li>

        <?php endforeach; ?>
        <!-- END: Results -->

    </ul>

    <div class="pagination-container">
        <?= AjaxLinkPager::widget(['pagination' => $pagination]); ?>
    </div>

</div>

<div class="modal-footer">
    <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
</div>

<script <?= Html::nonce() ?>>

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>
<?php ModalDialog::end() ?>

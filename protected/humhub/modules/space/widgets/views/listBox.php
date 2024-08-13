<?php

use humhub\widgets\AjaxLinkPager;
use humhub\widgets\ModalDialog;
use humhub\libs\Html;

/* @var $spaces \humhub\modules\space\models\Space[] */
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
                            <img class="media-object img-rounded pull-left"
                                 src="<?= $space->getProfileImage()->getUrl(); ?>" width="50"
                                 height="50" style="width: 50px; height: 50px;">

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
    <script <?= Html::nonce() ?>>

        // scroll to top of list
        $(".modal-body").animate({scrollTop: 0}, 200);

    </script>
<?php ModalDialog::end() ?>




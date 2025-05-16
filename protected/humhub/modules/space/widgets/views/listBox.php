<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\AjaxLinkPager;
use humhub\widgets\modal\Modal;

/* @var $spaces Space[] */
?>


<?php Modal::beginDialog(['title' => $title]) ?>

    <?php if (count($spaces) === 0) : ?>
        <p><?= Yii::t('SpaceModule.base', 'No spaces found.'); ?></p>
    <?php endif; ?>

    <div id="spacelist-content" class="hh-list">
        <!-- BEGIN: Results -->
        <?php foreach ($spaces as $space) : ?>
            <a href="<?= $space->getUrl() ?>" data-modal-close="1" class="d-flex">
                <div class="flex-shrink-0 me-2">
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
        <?= AjaxLinkPager::widget(['pagination' => $pagination]) ?>
    </div>

    <script <?= Html::nonce() ?>>
        // scroll to top of list
        $(".modal-body").animate({scrollTop: 0}, 200);
    </script>

<?php Modal::endDialog() ?>

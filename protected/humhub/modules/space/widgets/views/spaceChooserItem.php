<?php

/* @var $space \humhub\modules\space\models\Space */

use humhub\modules\space\widgets\Image;
use humhub\libs\Helpers;
use yii\helpers\Html;
?>

<li<?= (!$visible) ? ' style="display:none"' : '' ?> data-space-chooser-item <?= $data ?> data-space-guid="<?= $space->guid; ?>">
    <a href="<?= $space->getUrl(); ?>">
        <div class="media">
            <?= Image::widget([
                'space' => $space,
                'width' => 24,
                'htmlOptions' => [
                    'class' => 'pull-left',
            ]]);
            ?>
            <div class="media-body">
                <strong class="space-name"><?= Html::encode($space->name); ?></strong>
                    <?= $badge ?>
                <div data-message-count="<?= $updateCount; ?>" style="display: none;" class="badge badge-space messageCount pull-right tt" title="<?= Yii::t('SpaceModule.chooser', '{n,plural,=1{# new entry} other{# new entries}} since your last visit', ['n' => $updateCount]); ?>">
                    <?= $updateCount; ?>
                </div>
                <br>
                <p><?= Html::encode(Helpers::truncateText($space->description, 60)); ?></p>
                <?php if ($space->hasTags()) : ?>
                    <div class="space-tags" style="display:none">
                        <div class="label label-default"><?= implode('</div> <div class="label label-default">', $space->getTags()); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </a>
</li>

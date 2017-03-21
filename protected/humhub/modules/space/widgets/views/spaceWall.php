<?php

use yii\helpers\Html;
?>
<div class="panel panel-default">
    <div class="panel-body">

        <div class="media">
            <a href="<?= $space->getUrl(); ?>" class="pull-left">
                <!-- Show space image -->
                <?= \humhub\modules\space\widgets\Image::widget([
                    'space' => $space,
                    'width' => 40,
                ]); ?>
            </a>
            <div class="media-body">
                <!-- show username with link and creation time-->
                <h4 class="media-heading"><a href="<?= $space->getUrl(); ?>"><?= Html::encode($space->displayName); ?></a> </h4>
                <h5><?= Html::encode($space->description); ?></h5>
            </div>
        </div>

    </div>
</div>

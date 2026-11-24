<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

/* @var $space Space */

?>
<?php if (!empty($space->getTags())) : ?>
    <div id="user-tags-panel" class="card content-card">

        <?= PanelMenu::widget() ?>

        <div class="card-header"><div class="card-title"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> tags') ?></div></div>
        <div class="card-body">
            <div class="tags">
                <?php foreach ($space->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'q' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

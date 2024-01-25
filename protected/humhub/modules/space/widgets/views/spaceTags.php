<?php

use humhub\widgets\PanelMenu;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $space \humhub\modules\space\models\Space */

?>
<?php if (!empty($space->getTags())): ?>
    <div id="user-tags-panel" class="card card-default">
        <?= PanelMenu::widget(['id' => 'space-tags-panel']); ?>

        <div class="card-header"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> tags'); ?></div>
        <div class="card-body">
            <div class="tags">
                <?php foreach ($space->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'keyword' => $tag]), ['class' => 'btn btn-outline-secondary btn-sm tag']); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

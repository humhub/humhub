<?php

use humhub\widgets\PanelMenu;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $space \humhub\modules\space\models\Space */

?>
<?php if (!empty($space->getTags())) : ?>
    <div id="user-tags-panel" class="panel panel-default">

        <?= PanelMenu::widget(['id' => 'space-tags-panel']); ?>

        <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> tags'); ?></div>
        <div class="panel-body">
            <div class="tags">
                <?php foreach ($space->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'keyword' => $tag]), ['class' => 'btn btn-default btn-xs tag']); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

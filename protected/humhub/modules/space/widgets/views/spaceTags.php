<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

/* @var $space Space */

?>
<?php if (!empty($space->getTags())) : ?>
    <div id="user-tags-panel" class="panel panel-default">

        <?= PanelMenu::widget([
            'id' => 'user-tags-panel',
            'enableCollapseOption' => true,
        ]) ?>

        <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> tags') ?></div>
        <div class="panel-body collapse">
            <div class="tags">
                <?php foreach ($space->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'keyword' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

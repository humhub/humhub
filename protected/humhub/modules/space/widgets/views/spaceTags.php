<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\SidebarCard;
use yii\helpers\Url;

/* @var $space Space */

?>
<?php if (!empty($space->getTags())) : ?>
    <?php SidebarCard::begin([
        'title' => Yii::t('SpaceModule.base', '<strong>Space</strong> tags'),
        'encodeTitle' => false,
        'options' => ['id' => 'user-tags-panel'],
    ]) ?>
            <div class="tags">
                <?php foreach ($space->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'q' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?>
                <?php endforeach; ?>
            </div>
    <?php SidebarCard::end() ?>
<?php endif; ?>

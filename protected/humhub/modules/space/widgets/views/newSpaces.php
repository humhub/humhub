<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\SidebarCard;

/* @var $newSpaces Space[] */
/* @var $showMoreButton bool */
?>

<?php SidebarCard::begin([
    'title' => Yii::t('SpaceModule.base', '<strong>New</strong> Spaces'),
    'encodeTitle' => false,
    'options' => ['id' => 'new-spaces-panel', 'class' => 'spaces'],
]) ?>
        <?php foreach ($newSpaces as $space) : ?>
            <?= Image::widget([
                'space' => $space,
                'showTooltip' => true,
                'width' => 40,
                'link' => true,
                'htmlOptions' => [
                    'style' => 'margin-bottom: 5px;',
                ],
            ]) ?>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br/>
            <br/>
            <?= Html::a(Yii::t('SpaceModule.base', 'See all'), ['/space/spaces'], ['class' => 'btn btn-xl btn-primary']) ?>
        <?php endif; ?>
<?php SidebarCard::end() ?>

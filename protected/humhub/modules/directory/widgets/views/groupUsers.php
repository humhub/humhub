<?php

/* @var $this \yii\web\View */
/* @var $group humhub\modules\user\models\Group */
/* @var $users humhub\modules\user\models\User[] */
/* @var $showMoreurl string */

use humhub\libs\Html;
use humhub\modules\user\widgets\Image;
?>
<?php foreach ($users as $user): ?>
    <?= Image::widget(['user' => $user, 'width' => 40, 'showTooltip' => true]); ?>
<?php endforeach; ?>

<?php if (!empty($showMoreUrl)) : ?>
    <?= Html::a(Yii::t('DirectoryModule.base', "show all members"), $showMoreUrl, ['class' => 'btn btn-sm btn-default']); ?>
<?php endif; ?>
<?php
/* @var $this \yii\web\View */
/* @var $groups humhub\modules\user\models\Group[] */

use yii\helpers\Html;
use humhub\modules\directory\widgets\GroupUsers;
?>
<div class="panel panel-default groups">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', '<strong>Member</strong> Group Directory'); ?>
    </div>

    <div class="panel-body">
        <?php foreach ($groups as $group) : ?>
            <h1><?= Html::encode($group->name); ?></h1>
            <?= GroupUsers::widget(['group' => $group]); ?>
            <hr />
        <?php endforeach; ?>
    </div>

</div>


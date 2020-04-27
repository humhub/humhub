<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\ChallengeMenu;
use yii\helpers\Html;

AdminMenu::markAsActive(['/admin/challenge']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> Challenges'); ?>
    </div>
    <?= ChallengeMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
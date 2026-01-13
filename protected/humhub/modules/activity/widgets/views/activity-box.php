<?php

use humhub\widgets\PanelMenu;

/* @var $this \humhub\components\View */
/* @var $activities string[] */

?>
<div class="panel panel-default panel-activities" id="panel-activities">
    <?= PanelMenu::widget() ?>
    <div class="panel-heading">
        <?= Yii::t('ActivityModule.base', '<strong>Latest</strong> activities') ?>
    </div>
    <div>
        <div id="activityContents" class="hh-list activities">
            <?php foreach ($activities as $activity): ?>
                <?= $activity; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

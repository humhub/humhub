<?php

use humhub\modules\activity\services\RenderService;
use humhub\widgets\PanelMenu;

/* @var $this \humhub\components\View */
/* @var $activities \humhub\modules\activity\models\Activity[] */

?>
<div class="panel panel-default panel-activities" id="panel-activities">
    <?= PanelMenu::widget() ?>
    <div class="panel-heading">
        <?= Yii::t('ActivityModule.base', '<strong>Latest</strong> activities') ?>
    </div>
    <div>
        <div id="activity-box-content" class="hh-list activities">
            <?php if (empty($activities)): ?>
                <?= Yii::t('ActivityModule.base', 'There are no activities yet.'); ?>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <?= (new RenderService($activity))->getWeb(); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

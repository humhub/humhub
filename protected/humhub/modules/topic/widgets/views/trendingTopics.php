<?php

use humhub\widgets\PanelMenu;
use humhub\modules\topic\widgets\TopicLabel;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $topics \humhub\modules\topic\models\Topic[] */

?>
<div class="panel panel-default panel-topic" id="panel-topic">
    <?= PanelMenu::widget(['id' => 'panel-topic']) ?>
    <div class="panel-heading">
        <?= Yii::t('TopicModule.base', '<strong>Most</strong> Used Topics') ?>
    </div>
    <div class="panel-body">
        <?php foreach ($topics as $topic): ?>
            <?= TopicLabel::forTopic($topic) ?>
        <?php endforeach; ?>
    </div>
</div>

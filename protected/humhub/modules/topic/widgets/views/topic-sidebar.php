<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicBadge;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\PanelMenu;

/* @var Topic[] $topics */
/* @var string $showMoreUrl */
?>
<div class="panel panel-default panel-topic-sidebar" id="panel-topic-sidebar" data-ui-widget="topic.sidebar.Sidebar" data-ui-init>
    <?= PanelMenu::widget(['id' => 'panel-topic-sidebar']) ?>
    <div class="panel-heading">
        <?= Icon::get('star') . ' ' . Yii::t('TopicModule.base', '<strong>Popular</strong> topics') ?>
    </div>

    <div class="panel-body">
        <div class="topic-label-list d-flex gap-1 flex-wrap">
            <?php foreach ($topics as $topic) : ?>
                <?= TopicBadge::forTopic($topic) ?>
            <?php endforeach ?>
        </div>

        <?php if ($showMoreUrl) : ?>
            <?= Button::light(Yii::t('TopicModule.base', 'Show more'))
                ->action('showMore', $showMoreUrl)
                ->cssClass('w-100')
                ->sm() ?>
        <?php endif ?>
    </div>
</div>
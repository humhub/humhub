<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicBadge;
use humhub\widgets\Icon;
use humhub\widgets\SidebarCard;

/* @var Topic[] $topics */
?>
<?php SidebarCard::begin([
    'title' => Icon::get('star') . ' ' . Yii::t('TopicModule.base', '<strong>Popular</strong> topics'),
    'encodeTitle' => false,
    'options' => ['id' => 'panel-topic-sidebar', 'class' => 'panel-topic-sidebar'],
]) ?>
    <div class="topic-label-list d-flex gap-1 flex-wrap m-0">
        <?php foreach ($topics as $topic) : ?>
            <?= TopicBadge::forTopic($topic) ?>
        <?php endforeach ?>
    </div>
<?php SidebarCard::end() ?>
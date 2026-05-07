<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\PanelMenu;

/* @var User[] $followers */
/* @var int $totalFollowerCount */
/* @var array $showListOptions */

$title = Yii::t('SpaceModule.base', '<strong>Space</strong> followers');
?>

<div class="panel panel-default follower" id="space-follower-panel">
    <?= PanelMenu::widget([
        'extraMenus' => Html::tag('li', Link::to(Yii::t('SpaceModule.base', 'Show as List'))->icon('list')->options($showListOptions)),
        'panelLabel' => $title,
    ]) ?>

    <div class="panel-heading"<?= Html::renderTagAttributes($showListOptions + ['style' => 'cursor:pointer']) ?>>
        <?= $title ?> (<?= $totalFollowerCount ?>)
    </div>

    <div class="panel-body">
        <?php foreach ($followers as $follower): ?>
            <?= $follower->getProfileImage()->render(32, [
                'class' => 'rounded tt img_margin',
                'showTooltip' => true,
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>

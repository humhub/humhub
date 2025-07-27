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
?>
<div class="panel panel-default follower" id="space-follower-panel">
    <?= PanelMenu::widget([
        'id' => 'space-follower-panel',
        'enableCollapseOption' => true,
        'extraMenus' => Html::tag('li', Link::asLink(Yii::t('SpaceModule.base', 'Show as List'))->icon('list')->options($showListOptions)),
    ]) ?>

    <div class="panel-heading"<?= Html::renderTagAttributes($showListOptions + ['style' => 'cursor:pointer']) ?>>
        <?= Yii::t('SpaceModule.base', '<strong>Space</strong> followers') ?> (<?= $totalFollowerCount ?>)
    </div>

    <div class="panel-body collapse">
        <?php foreach ($followers as $follower): ?>
            <?= $follower->getProfileImage()->render(32, [
                'class' => 'rounded tt img_margin',
                'showTooltip' => true,
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>

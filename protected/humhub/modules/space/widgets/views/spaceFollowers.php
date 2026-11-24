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
<div class="card content-card follower" id="space-follower-panel">
    <?= PanelMenu::widget([
        'extraMenus' => Html::tag('li', Link::to(Yii::t('SpaceModule.base', 'Show as List'))->icon('list')->options($showListOptions)),
    ]) ?>

    <div class="card-header"<?= Html::renderTagAttributes($showListOptions + ['style' => 'cursor:pointer']) ?>>
        <div class="card-title"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> followers') ?> (<?= $totalFollowerCount ?>)</div>
    </div>

    <div class="card-body">
        <?php foreach ($followers as $follower): ?>
            <?= $follower->getProfileImage()->render(32, [
                'class' => 'rounded tt img_margin',
                'showTooltip' => true,
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>

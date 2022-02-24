<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\user\models\User;
use humhub\widgets\PanelMenu;
use yii\helpers\Html;

/* @var User[] $followers */
/* @var int $totalFollowerCount */
?>
<div class="panel panel-default follower" id="space-follower-panel">
    <?= PanelMenu::widget(['id' => 'space-follower-panel']); ?>

    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>Space</strong> followers'); ?> (<?= $totalFollowerCount ?>)</div>

    <div class="panel-body">
        <?php foreach ($followers as $follower): ?>
            <?= $follower->getProfileImage()->render(32, [
                'class' => 'img-rounded tt img_margin',
                'showTooltip' => true,
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>
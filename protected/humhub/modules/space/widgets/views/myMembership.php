<?php

use humhub\widgets\PanelMenu;

/**
 * @var string $role
 * @var string $memberSince
 */

$title = Yii::t('SpaceModule.base', '<strong>About</strong> your membership');
?>

<div class="panel panel-default panel-my-membership" id="my-membership-panel">
    <?= PanelMenu::widget(['panelLabel' => $title]) ?>
    <div class="panel-heading">
        <?= $title ?>
    </div>
    <div class="panel-body">
        <p>
            <b><?= Yii::t('SpaceModule.base', 'Role') ?>: </b>
            <?= Yii::t('SpaceModule.base', ucfirst($role)) ?>
        </p>
        <?php if (!empty($memberSince)): ?>
            <p><b><?= Yii::t('SpaceModule.base', 'Member since') ?>: </b><?= $memberSince ?></p>
        <?php endif; ?>
    </div>
</div>

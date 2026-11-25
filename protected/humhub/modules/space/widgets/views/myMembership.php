<?php

use humhub\widgets\PanelMenu;

/**
 * @var string $role
 * @var string $memberSince
 */
?>

<div class="card content-card panel-my-membership" id="my-membership-panel">
    <?= PanelMenu::widget() ?>
    <div class="card-header">
        <div class="card-title"><?= Yii::t('SpaceModule.base', '<strong>About</strong> your membership') ?></div>
    </div>
    <div class="card-body">
        <p>
            <b><?= Yii::t('SpaceModule.base', 'Role') ?>: </b>
            <?= Yii::t('SpaceModule.base', ucfirst($role)) ?>
        </p>
        <?php if (!empty($memberSince)): ?>
            <p><b><?= Yii::t('SpaceModule.base', 'Member since') ?>: </b><?= $memberSince ?></p>
        <?php endif; ?>
    </div>
</div>

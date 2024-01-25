<?php

use humhub\widgets\PanelMenu;

/**
 * @var string $role
 * @var string $memberSince
 */
?>

<div class="card card-default card-my-membership" id="my-membership-panel">
    <?= PanelMenu::widget(['id' => 'space-my-membership-panel']); ?>
    <div class="card-header"><?= Yii::t('SpaceModule.base', '<strong>About</strong> your membership'); ?></div>
    <div class="card-body">
        <p><b><?= Yii::t('SpaceModule.base', 'Role') ?>: </b><?= ucfirst($role) ?></p>
        <?php if (!empty($memberSince)): ?>
            <p><b><?= Yii::t('SpaceModule.base', 'Member since') ?>: </b><?= $memberSince ?></p>
        <?php endif; ?>
    </div>
</div>

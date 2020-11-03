<?php

use humhub\widgets\PanelMenu;

/**
 * @var string $role
 * @var string $memberSince
 */
?>

<div class="panel panel-default panel-my-membership" id="my-membership-panel">
    <?= PanelMenu::widget(['id' => 'space-my-membership-panel']); ?>
    <div class="panel-heading"><?= Yii::t('SpaceModule.base', '<strong>About</strong> your membership'); ?></div>
    <div class="panel-body">

        <p><b><?= Yii::t('SpaceModule.base', 'Role') ?>: </b><?= ucfirst($role) ?></p>
        <?php if (!empty($memberSince)): ?>
            <p><b><?= Yii::t('SpaceModule.base', 'Member since') ?>: </b><?= $memberSince ?></p>
        <?php endif; ?>
    </div>
</div>

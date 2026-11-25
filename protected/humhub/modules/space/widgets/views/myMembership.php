<?php

use humhub\widgets\SidebarCard;

/**
 * @var string $role
 * @var string $memberSince
 */
?>

<?php SidebarCard::begin([
    'title' => Yii::t('SpaceModule.base', '<strong>About</strong> your membership'),
    'encodeTitle' => false,
    'options' => ['id' => 'my-membership-panel', 'class' => 'panel-my-membership'],
]) ?>
        <p>
            <b><?= Yii::t('SpaceModule.base', 'Role') ?>: </b>
            <?= Yii::t('SpaceModule.base', ucfirst($role)) ?>
        </p>
        <?php if (!empty($memberSince)): ?>
            <p><b><?= Yii::t('SpaceModule.base', 'Member since') ?>: </b><?= $memberSince ?></p>
        <?php endif; ?>
<?php SidebarCard::end() ?>

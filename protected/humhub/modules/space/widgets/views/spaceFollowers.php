<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\user\models\User;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\SidebarCard;

/* @var User[] $followers */
/* @var int $totalFollowerCount */
/* @var array $showListOptions */
?>
<?php SidebarCard::begin([
    'title' => Yii::t('SpaceModule.base', '<strong>Space</strong> followers') . ' (' . $totalFollowerCount . ')',
    'encodeTitle' => false,
    'titleAction' => $showListOptions,
    'menu' => [new MenuLink(['label' => Yii::t('SpaceModule.base', 'Show as List'), 'icon' => 'list', 'url' => '#', 'htmlOptions' => $showListOptions])],
    'options' => ['id' => 'space-follower-panel', 'class' => 'follower'],
]) ?>
    <?php foreach ($followers as $follower): ?>
        <?= $follower->getProfileImage()->render(32, [
            'class' => 'rounded tt img_margin',
            'showTooltip' => true,
        ]) ?>
    <?php endforeach; ?>
<?php SidebarCard::end() ?>

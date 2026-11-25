<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\SidebarCard;

/* @var User[] $users */
/* @var bool $showListButton */
/* @var string $urlMembersList */
/* @var array $privilegedUserIds */
/* @var int $totalMemberCount */
/* @var array $showListOptions */

?>
<?php SidebarCard::begin([
    'title' => Yii::t('SpaceModule.base', '<strong>Space</strong> members') . ' (' . $totalMemberCount . ')',
    'encodeTitle' => false,
    'titleAction' => $showListOptions,
    'menu' => [new MenuLink(['label' => Yii::t('SpaceModule.base', 'Show as List'), 'icon' => 'list', 'url' => '#', 'htmlOptions' => $showListOptions])],
    'options' => ['id' => 'space-members-panel', 'class' => 'members'],
]) ?>
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($users as $user) : ?>
                <?php
                $imageWidgetConfig = [
                    'user' => $user, 'width' => 32,
                    'showTooltip' => true,
                    'showSelfOnlineStatus' => true,
                ];
                if (in_array($user->id, $privilegedUserIds[Space::USERGROUP_OWNER])) {
                    // Show Owner image & tooltip
                    echo Image::widget(array_merge($imageWidgetConfig, [
                        'tooltipText' => Yii::t('SpaceModule.base', 'Owner:') . "\n" . Html::encode($user->displayName),
                        'imageOptions' => ['style' => 'border:1px solid var(--bs-success)'],
                    ]));
                } elseif (in_array($user->id, $privilegedUserIds[Space::USERGROUP_ADMIN])) {
                    // Show Admin image & tooltip
                    echo Image::widget(array_merge($imageWidgetConfig, [
                        'tooltipText' => Yii::t('SpaceModule.base', 'Administrator:') . "\n" . Html::encode($user->displayName),
                        'imageOptions' => ['style' => 'border:1px solid var(--bs-success)'],
                    ]));
                } elseif (in_array($user->id, $privilegedUserIds[Space::USERGROUP_MODERATOR])) {
                    // Show Moderator image & tooltip
                    echo Image::widget(array_merge($imageWidgetConfig, [
                        'tooltipText' => Yii::t('SpaceModule.base', 'Moderator:') . "\n" . Html::encode($user->displayName),
                        'imageOptions' => ['style' => 'border:1px solid var(--bs-info)'],
                    ]));
                } else {
                    // Standard member
                    echo Image::widget($imageWidgetConfig);
                }
                ?>
            <?php endforeach; ?>
        </div>

        <?php if ($showListButton) : ?>
            <a href="<?= $urlMembersList ?>"
               data-bs-target="#globalModal"
               class="btn btn-light btn-sm mt-2">
                <?= Yii::t('SpaceModule.base', 'Show all') ?>
            </a>
        <?php endif; ?>
<?php SidebarCard::end() ?>

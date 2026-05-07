<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Link;

/**
 * @var $this View
 * @var $enableCollapseOption bool
 * @var $enableHideOption bool
 * @var $hidePanel bool
 * @var $panelId string
 * @var $panelLabel string
 */

?>

<?= Html::beginTag('ul', [
    'class' => 'nav nav-pills preferences',
    'data-ui-widget' => 'ui.panel.PanelMenu',
    'data-ui-init' => '',
    'data-hide-panel' => $hidePanel ? 1 : 0,
]) ?>
    <li class="nav-item dropdown">
        <?= Html::a('', '#', [
            'class' => 'nav-link dropdown-toggle',
            'data-bs-toggle' => 'dropdown',
            'aria-label' => Yii::t('base', 'Toggle panel menu'),
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
            'role' => 'button',
        ]) ?>

        <ul class="dropdown-menu dropdown-menu-end">
            <?php if ($enableCollapseOption): ?>
                <li>
                    <?= Html::a('', '#' . $panelId, [
                        'class' => ['dropdown-item', 'panel-collapse'],
                        'data-bs-toggle' => 'collapse',
                        'aria-controls' => $panelId,
                        'aria-expanded' => 'false',
                    ]) ?>
                </li>
            <?php endif; ?>
            <?php if ($enableHideOption): ?>
                <li>
                    <?= Link::to(
                        Yii::t('base', 'Hide'),
                        [
                            '/panel-menu/hide',
                            'panelId' => $panelId,
                            'panelLabel' => $panelLabel,
                            'ajax' => 1,
                        ],
                    )
                        ->encodeLabel(false)
                        ->icon('eye-slash')
                        ->action('humhub.ui.panel.hidePanel')
                        ->confirm(
                            Icon::get('eye-slash') . ' ' . Yii::t('base', ' Hide panel'),
                            Yii::t('base', 'You can show it again in your account "Settings" ' . Icon::get('caret-right') . ' "General".'),
                            Yii::t('base', 'Confirm'),
                            Yii::t('base', 'Cancel'),
                        )
                        ->cssClass(['dropdown-item']) ?>
                </li>
            <?php endif; ?>
            <?= $this->context->extraMenus ?>
        </ul>
    </li>
<?= Html::endTag('ul') ?>

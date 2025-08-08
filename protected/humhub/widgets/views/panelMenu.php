<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\bootstrap\Link;

/**
 * @var $this View
 * @var $enableCollapseOption bool
 * @var $collapseId string
 */

?>

<ul data-ui-widget="ui.panel.PanelMenu" data-ui-init class="nav nav-pills preferences">
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
                    <?= Link::instance()
                        ->link('#' . $collapseId)
                        ->cssClass(['btn', 'dropdown-item', 'panel-collapse'])
                        ->options([
                            'data-bs-toggle' => 'collapse',
                            'aria-controls' => $collapseId,
                            'aria-expanded' => 'false',
                            'role' => 'button',
                        ]) ?>
                </li>
            <?php endif; ?>
            <?= $this->context->extraMenus ?>
        </ul>
    </li>
</ul>

<?php

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Link;

/* @var $id string */

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

        <ul class="dropdown-menu float-end">
            <li>
                <?= Link::instance()->action('toggle')->cssClass(['dropdown-item', 'panel-collapse'])?>
            </li>
            <?= $this->context->extraMenus ?>
        </ul>
    </li>
</ul>

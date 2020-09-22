<?php
use yii\helpers\Html;
use humhub\widgets\Link;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $id string */

?>

<ul data-ui-widget="ui.panel.PanelMenu" data-ui-init class="nav nav-pills preferences">
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"
           aria-label="<?= Yii::t('base', 'Toggle panel menu'); ?>" aria-haspopup="true"><i class="fa fa-angle-down"></i>
        </a>
        <ul class="dropdown-menu pull-right">
            <li>
                <?= Link::instance()->action('toggle')->cssClass('panel-collapse')?>
            </li>
            <?= $this->context->extraMenus; ?>
        </ul>
    </li>
</ul>

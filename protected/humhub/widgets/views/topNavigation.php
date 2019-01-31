<?php

use yii\helpers\Html;

/* @var $this \humhub\components\View */
/* @var $menu \humhub\widgets\TopMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
?>

<?php foreach ($entries as $entry) : ?>
    <li class="visible-md visible-lg <?php if ($entry->getIsActive()): ?>active<?php endif; ?>">
        <?= Html::a($entry->getIcon() . '<br />' . $entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions()); ?>
    </li>
<?php endforeach; ?>

<li class="dropdown visible-xs visible-sm">
    <a href="#" id="top-dropdown-menu" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-align-justify"></i><br>
        <?= Yii::t('base', 'Menu'); ?>
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
        <?php foreach ($entries as $entry) : ?>
            <li class="<?php if ($entry->getIsActive()): ?>active<?php endif; ?>">
                <?= $entry->render(); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</li>

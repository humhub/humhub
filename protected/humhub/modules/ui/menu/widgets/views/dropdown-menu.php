<?php

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropDownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
?>

<div class="btn-group dropdown-navigation">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">

        <?= $menu->label ?>
        <span class="caret"></span>
   </button>

    <ul class="dropdown-menu pull-right">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->renderLinkTag() ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

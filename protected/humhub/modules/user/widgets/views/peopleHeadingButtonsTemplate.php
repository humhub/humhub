<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\user\widgets\PeopleHeadingButtons;
use humhub\widgets\bootstrap\Button;

/* @var $this View */
/* @var $menu PeopleHeadingButtons */
/* @var $entries MenuEntry[] */
?>

<?php foreach ($entries as $entry) : ?>
    <?php
    $htmlOptions = $entry->getHtmlOptions();
    if ($entry->getIsActive()) {
        $htmlOptions['active'] = '';
    }
    ?>
    <div style="margin-left: 10px" class="float-end">
        <?= Button::accent($entry->getIcon() . '&nbsp;&nbsp;' . Html::tag('strong', $entry->getLabel()))
            ->link($entry->getUrl())
            ->options($htmlOptions)
            ->sm() ?>
    </div>
<?php endforeach; ?>

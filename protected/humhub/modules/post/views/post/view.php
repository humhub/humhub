<?php

use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\stream\assets\StreamAsset;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $post \humhub\modules\post\models\Post */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */

StreamAsset::register($this);
?>

<div data-action-component="stream.SimpleStream">
    <?= StreamEntryWidget::renderStreamEntry($post) ?>
</div>



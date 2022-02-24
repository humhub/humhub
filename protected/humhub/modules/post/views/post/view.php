<?php

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\post\models\Post;
use humhub\modules\stream\assets\StreamAsset;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $post Post */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $renderOptions StreamEntryOptions */

StreamAsset::register($this);
?>

<div data-action-component="stream.SimpleStream">
    <?= StreamEntryWidget::renderStreamEntry($post, $renderOptions) ?>
</div>



<?php

use humhub\components\View;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallEntryAddons;

/* @var $this View */
/* @var $model ContentActiveRecord */
/* @var $renderOptions WallStreamEntryOptions */

?>

<?php if (!$renderOptions->isAddonsDisabled()) : ?>
    <div class="stream-entry-addons clearfix">
        <?= WallEntryAddons::widget(['object' => $model, 'renderOptions' => $renderOptions]) ?>
    </div>
<?php endif; ?>

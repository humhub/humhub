<?php

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $model ContentActiveRecord */
/* @var $addonOptions array|boolean */


$renderAddons = $addonOptions !== false;

?>

<?php if ($renderAddons) : ?>
    <div class="stream-entry-addons clearfix">
        <?= WallEntryAddons::widget(array_merge($addonOptions, ['object' => $model])) ?>
    </div>
<?php endif; ?>

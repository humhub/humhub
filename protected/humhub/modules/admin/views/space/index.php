<?php

use humhub\components\View;
use humhub\modules\admin\widgets\SpaceTable;
use humhub\widgets\ContentCard;

/* @var $this View */

// The table island is a content card of its own
ContentCard::markRendered($this);
?>
<?= SpaceTable::widget() ?>

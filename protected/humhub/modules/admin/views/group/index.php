<?php

use humhub\components\View;
use humhub\modules\admin\widgets\GroupTable;
use humhub\widgets\ContentCard;

/* @var $this View */

// The table island is a content card of its own
ContentCard::markRendered($this);
?>
<?= GroupTable::widget() ?>

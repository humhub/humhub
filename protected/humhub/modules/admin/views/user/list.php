<?php

use humhub\components\View;
use humhub\modules\admin\widgets\UserTable;
use humhub\widgets\ContentCard;

/* @var $this View */

// The table island is a content card of its own
ContentCard::markRendered($this);
?>
<?= UserTable::widget() ?>

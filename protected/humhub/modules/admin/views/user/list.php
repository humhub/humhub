<?php

use humhub\modules\admin\widgets\UserTable;

/**
 * @var $this \humhub\components\View
 */

// The table is a content card of its own, not inside the layout's panel.
$this->params['adminContentCard'] = true;
?>
<?= UserTable::widget() ?>

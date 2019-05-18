<?php
use humhub\libs\Html;

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
?>

<script <?= Html::nonce() ?>>
    $(document).one('humhub:ready', function() {
        if(humhub && humhub.modules.notification && humhub.modules.notification.menu) {
            humhub.modules.notification.menu.updateCount(<?= $count ?>);
        }
    });
</script>
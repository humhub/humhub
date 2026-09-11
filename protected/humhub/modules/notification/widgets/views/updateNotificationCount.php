<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;

/* @var int $count */

// Pushes the current unread count into the already-mounted notification island (the top menu is
// not re-rendered by a pjax navigation, so its count would otherwise go stale). A pushed count
// rather than a refetch: the number is free here, a request per navigation would not be.
?>
<script <?= Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        if (humhub.modules.vue && humhub.modules.vue.events) {
            humhub.modules.vue.events.trigger('humhub:notification:setCount', [<?= (int)$count ?>]);
        }
    });
</script>

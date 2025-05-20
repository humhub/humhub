<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\tour\models\TourParams;

/**
 * @var $this View
 * @var $params array
 */
?>

<script <?= Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                page: '<?= $params[TourParams::KEY_PAGE] ?>',
                nextUrl: '<?= TourParams::getNextUrl($params) ?>',
                driver: <?= json_encode($params[TourParams::KEY_DRIVER], JSON_THROW_ON_ERROR) ?>
            }
        );
    });
</script>

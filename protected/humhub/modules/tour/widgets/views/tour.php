<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\tour\TourConfig;

/**
 * @var $this View
 * @var $config array
 */

?>

<script <?= Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                tourId: '<?= TourConfig::getTourId($config) ?>',
                nextUrl: '<?= TourConfig::getNextUrl($config) ?>',
                driverJs: <?= json_encode(TourConfig::getDriverJs($config), JSON_THROW_ON_ERROR) ?>
            }
        );
    });
</script>

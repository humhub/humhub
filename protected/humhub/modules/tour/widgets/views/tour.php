<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\tour\models\TourConfig;

/**
 * @var $this View
 * @var $config array
 */
?>

<script <?= Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                page: '<?= $config[TourConfig::KEY_PAGE] ?>',
                nextUrl: '<?= TourConfig::getNextUrl($config[TourConfig::KEY_NEXT_PAGE]) ?>',
                driver: <?= json_encode($config[TourConfig::KEY_DRIVER], JSON_THROW_ON_ERROR) ?>
            }
        );
    });
</script>

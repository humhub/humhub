<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\marketplace\widgets\MarketplaceLink;

/* @var int $count */
?>
<div class="alert alert-warning d-flex flex-wrap align-items-center gap-3 mb-0 modules-updates-info">
    <div class="flex-grow-1">
        <strong><?= Yii::t('AdminModule.base', 'Updates available for {count} of your modules', ['count' => $count]) ?></strong><br>
        <?= Yii::t('AdminModule.base', 'Keep your system up-to-date and benefit from the latest improvements.') ?>
    </div>
    <?= MarketplaceLink::warning(Yii::t('AdminModule.base', 'Install Updates'))->icon('external-link')->sm() ?>
</div>

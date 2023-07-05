<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\marketplace\widgets\MarketplaceLink;

/* @var int $count */
?>
<div class="modules-updates-info">
    <?= MarketplaceLink::warning(Yii::t('AdminModule.base', 'Install updates'))->right()->sm() ?>
    <strong><?= Yii::t('AdminModule.base', 'Updates ready for {count} of your modules', ['count' => $count]) ?></strong><br>
    <?= Yii::t('AdminModule.base', 'Keep your system up-to-date and benefit from the latest improvements.') ?>
</div>

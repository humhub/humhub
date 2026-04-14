<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\marketplace\models\Licence;

/* @var $licence Licence */
?>
<div class="d-flex flex-wrap">
    <div class="pe-3 mb-3">
        <?= Html::img('@web-static/img/hh_professional.png', ['class' => 'hh-about-logo rounded-1']) ?>
    </div>
    <div class="mb-3 flex-grow-1">
        <h4>HumHub Professional Edition - SaaS</h4>
        <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Version:') . ' ' . Html::encode(Yii::$app->version) ?></div>
        <?php if (!empty($licence->licencedTo)) : ?>
            <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Licensed to:') . ' ' . Html::encode($licence->licencedTo) ?></div>
        <?php endif; ?>
        <?php if (!empty($licence->maxUsers)) : ?>
            <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Max. users:') . ' ' . Html::encode($licence->maxUsers) ?></div>
        <?php endif; ?>
    </div>
</div>
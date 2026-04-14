<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\marketplace\models\Licence;
use humhub\widgets\bootstrap\Button;

/* @var $licence Licence */
?>
<div class="d-flex flex-wrap">
    <div class="pe-3 mb-3">
        <?= Html::img('@web-static/img/hh_professional.png', ['class' => 'hh-about-logo rounded-1']) ?>
    </div>
    <div class="pe-3 mb-3 flex-grow-1">
        <h4>HumHub Professional Edition</h4>
        <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Version:') . ' ' . Html::encode(Yii::$app->version) ?></div>
        <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Licensed to:') . ' ' . Html::encode($licence->licencedTo) ?></div>
        <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Max. users:') . ' ' . Html::encode($licence->maxUsers) ?></div>
    </div>
    <div class="ms-auto mb-3 flex-sm-grow-0 flex-grow-1">
        <?= Button::primary(Yii::t('MarketplaceModule.base', 'Edit License'))
            ->link('/marketplace/licence')
            ->cssClass('w-100') ?>
    </div>
</div>
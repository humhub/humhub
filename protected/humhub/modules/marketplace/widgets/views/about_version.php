<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;

?>
<div class="d-flex flex-wrap">
    <div class="pe-3 mb-3">
        <?= Html::img('@web-static/img/hh_community.png', ['class' => 'hh-about-logo rounded-1']) ?>
    </div>
    <div class="pe-3 mb-3 flex-grow-1">
        <h4>HumHub Community Edition</h4>
        <div class="text-muted"><?= Yii::t('MarketplaceModule.base', 'Version:') . ' ' . Html::encode(Yii::$app->version) ?></div>
    </div>
    <div class="ms-auto mb-3 flex-sm-grow-0 flex-grow-1">
        <?= Button::success(Yii::t('MarketplaceModule.base', 'Add License'))
            ->link('/marketplace/licence')
            ->cssClass('w-100') ?>
    </div>
</div>
<?php

use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */
?>
<div style="padding:20px;min-height:164px" class="jumbotron">
    <div class="pull-left" style="padding-right:24px;">
        <img src="<?= Yii::getAlias('@web-static/img/humhub_pro.jpg'); ?>" style="height:124px;">
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span
        style="font-size:24px">Professional Edition - SaaS</span><br/>
    <span
        style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?> <?= Yii::$app->version ?></span><br/>
    <?php if (!empty($licence->licencedTo)): ?>
        <span
            style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Licenced to:'); ?> <?= $licence->licencedTo; ?></span>
        <br/>
    <?php endif; ?>
    <?php if (!empty($licence->maxUsers)): ?>
        <span
            style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Max. users:'); ?> <?= $licence->maxUsers ?></span>
    <?php endif; ?>
    <br/>

</div>

<?php

use yii\helpers\Url;

/* @var $this \humhub\components\View */

?>
<div style="padding:20px;min-height:164px" class="jumbotron">
    <div class="pull-left" style="padding-right:24px;">
        <img src="<?= Yii::getAlias('@web-static/img/humhub_pro.jpg'); ?>" style="height:124px;">
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span
        style="font-size:24px">Professional Edition - Demo</span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?> <?= Yii::$app->version ?></span><br/>
    <br/>
    <a href="<?= Url::to('https://www.humhub.com/professional-edition'); ?>" class="btn btn-success pull-right">
        <i class="fa fa-external-link" target="_blank">&nbsp;</i>
        <?= Yii::t('MarketplaceModule.base', 'More information'); ?>
    </a>
</div>

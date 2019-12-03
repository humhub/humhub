<?php

use yii\helpers\Url;

?>
<div style="padding:20px" class="jumbotron">

    <div class="pull-left" style="padding-right:12px">
        <img src="<?= Yii::getAlias('@web-static/img/humhub.png'); ?>" style="height:96px;">
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span style="font-size:24px">Community Edition</span><br/>
    <a href="<?= Url::to(['/marketplace/licence']); ?>" class="btn btn-success pull-right"><i
            class="fa fa-rocket">&nbsp;</i> <?= Yii::t('MarketplaceModule.base', 'Upgrade to Professional Edition'); ?>
    </a>
    <span
        style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?>&nbsp;<?= Yii::$app->version ?></span><br/>
    <br/>
</div>

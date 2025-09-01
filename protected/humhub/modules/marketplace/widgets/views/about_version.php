<?php

use yii\helpers\Url;

?>
<div style="padding:20px">

    <div class="float-start" style="padding-right:12px">
        <img src="<?= Yii::getAlias('@web-static/img/hh_community.png'); ?>" style="height:96px;">
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span style="font-size:24px">Community Edition</span><br/>
    <a href="<?= Url::to(['/marketplace/licence']); ?>" class="btn btn-success float-end"><i
            class="fa fa-rocket">&nbsp;</i> <?= Yii::t('MarketplaceModule.base', 'Upgrade to Professional Edition'); ?>
    </a>
    <span
        style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?>&nbsp;<?= Yii::$app->version ?></span><br/>
    <br/>
</div>

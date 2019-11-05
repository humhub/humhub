<?php

use humhub\modules\marketplace\models\Licence;
use yii\helpers\Url;

/* @var $this \humhub\components\View */
/* @var $licence Licence */

?>
<div style="padding:20px" class="jumbotron">
    <div class="pull-left" style="padding-right:24px">
        <img src="<?= Yii::getAlias('@web-static/img/humhub_pro.jpg'); ?>" style="height:124px;">
    </div>
    <div class="pull-right">
        <a href="<?= Url::to(['/marketplace/licence']); ?>" class="btn btn-primary btn-sm"><i class="fa fa-cogs">&nbsp;</i>
            <?= Yii::t('MarketplaceModule.base', 'Edit licence'); ?></a>
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span style="font-size:24px">Professional Edition</span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?> <?= Yii::$app->version ?></span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Licenced to:'); ?> <?= $licence->licencedTo; ?></span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Max. users:'); ?> <?= $licence->maxUsers; ?></span><br/>
</div>

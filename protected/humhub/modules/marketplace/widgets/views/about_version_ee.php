<?php

use humhub\modules\marketplace\models\Licence;
use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $licence Licence */

?>
<div style="padding:20px" class="jumbotron">
    <div class="pull-left" style="padding-right:24px">
        <img src="<?= Yii::getAlias('@web-static/img/humhub_pro.jpg'); ?>" style="height:124px;">
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span style="font-size:24px">Enterprise Edition</span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:'); ?> <?= Yii::$app->version ?></span><br/>

    <div class="pull-right">
        <a href="<?= Url::to(['/marketplace/licence']); ?>" class="btn btn-success"><i class="fa fa-rocket">&nbsp;</i>
            <?= Yii::t('MarketplaceModule.base', 'Upgrade to Professional Edition'); ?></a>
    </div>

    <br />
    <br />
    <br />
</div>

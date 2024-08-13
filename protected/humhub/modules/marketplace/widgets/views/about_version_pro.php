<?php

use humhub\libs\Html;
use humhub\modules\marketplace\models\Licence;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;

/* @var $this View */
/* @var $licence Licence */

?>
<div style="padding:20px" class="jumbotron">
    <div class="pull-left" style="padding-right:24px">
        <?= Html::img(Yii::getAlias('@web-static/img/humhub_pro.jpg'), ['style' => 'height:124px']) ?>
    </div>
    <div class="pull-right">
        <?= Button::primary(Yii::t('MarketplaceModule.base', 'Edit licence'))
            ->link(['/marketplace/licence'])->icon('cogs')->sm() ?>
    </div>
    <span style="font-size:36px">HumHub&nbsp;&nbsp;</span><span style="font-size:24px">Professional Edition</span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Version:') ?> <?=  Html::encode(Yii::$app->version) ?></span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Licenced to:') ?> <?= Html::encode($licence->licencedTo) ?></span><br/>
    <span style="font-size:18px"><?= Yii::t('MarketplaceModule.base', 'Max. users:') ?> <?=  Html::encode($licence->maxUsers) ?></span><br/>
</div>

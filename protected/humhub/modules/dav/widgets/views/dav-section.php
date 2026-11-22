<?php

use humhub\helpers\Html;

/* @var string $address */
/* @var string $username */
?>
<dl class="row mb-0">
    <dt class="col-sm-3"><?= Yii::t('DavModule.base', 'Address') ?></dt>
    <dd class="col-sm-9 font-monospace text-break"><?= Html::encode($address) ?></dd>
    <dt class="col-sm-3"><?= Yii::t('DavModule.base', 'Username') ?></dt>
    <dd class="col-sm-9 font-monospace text-break"><?= Html::encode($username) ?></dd>
    <dt class="col-sm-3"><?= Yii::t('DavModule.base', 'Password') ?></dt>
    <dd class="col-sm-9 mb-0"><?= Yii::t('DavModule.base', 'A personal access token with access to the files, one per device. Your account password does not work here.') ?></dd>
</dl>

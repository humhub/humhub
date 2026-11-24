<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/en/licences
 */

use humhub\components\access\ControllerAccess;
use humhub\helpers\Html;
use yii\helpers\Url;

?>

<div class="card content-card content-card-danger panel-invalid">
    <div class="card-header"><div class="card-title"><?= Yii::t('AdminModule.base', '<strong>Maintenance</strong> Mode'); ?></div></div>
    <div class="card-body">
        <p><?= ControllerAccess::getMaintenanceModeWarningText('<br>') ?></p>
        <br>
        <?php if (Yii::$app->user->isAdmin()): ?>
            <?= Html::a(Yii::t('AdminModule.base', 'Settings'), Url::toRoute(['/admin/setting']), ['class' => 'btn btn-danger']); ?>
        <?php endif; ?>
    </div>
</div>

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/en/licences
 */

use humhub\components\access\ControllerAccess;
use humhub\helpers\Html;
use humhub\widgets\SidebarCard;
use yii\helpers\Url;

?>

<?php SidebarCard::begin([
    'title' => Yii::t('AdminModule.base', '<strong>Maintenance</strong> Mode'),
    'encodeTitle' => false,
    'collapsible' => false,
    'options' => ['class' => 'content-card-danger panel-invalid'],
]) ?>
        <p><?= ControllerAccess::getMaintenanceModeWarningText('<br>') ?></p>
        <br>
        <?php if (Yii::$app->user->isAdmin()): ?>
            <?= Html::a(Yii::t('AdminModule.base', 'Settings'), Url::toRoute(['/admin/setting']), ['class' => 'btn btn-danger']); ?>
        <?php endif; ?>
<?php SidebarCard::end() ?>

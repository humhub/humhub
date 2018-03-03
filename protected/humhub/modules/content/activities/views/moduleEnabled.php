<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * @var $originator \humhub\modules\user\models\User|null
 * @var $moduleName string
 */

use yii\helpers\Html;

echo Yii::t(
    'ContentModule.activities',
    "%username% installed a new Module %modulename%",
    [
        '%username%' => $originator === null ? '' : Html::tag('b', Html::encode($originator->getDisplayName())),
        '%modulename%' => '<strong>' . Html::encode($moduleName) . '</strong>',
    ]);

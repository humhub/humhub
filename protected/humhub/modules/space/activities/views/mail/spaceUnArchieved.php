<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

echo Yii::t('ActivityModule.base', '{spaceName} has been unarchived', [
    '{spaceName}' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
]);

?>
<br/>

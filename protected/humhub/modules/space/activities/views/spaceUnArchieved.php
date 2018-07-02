<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', '%spaceName% has been unarchived', [
    '%spaceName%' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
]);

?>
<br/>






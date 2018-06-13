<?php

use yii\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\content\components\ContentContainerController;

echo Yii::t('ActivityModule.views_activities_ActivitySpaceMemberAdded', '%spaceName% has been unarchived', [
    '%spaceName%' => '<strong>' . Html::encode(Helpers::truncateText($source->name, 40)) . '</strong>'
]
);




<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
 
/* @var $this humhub\components\View */

\humhub\modules\activity\assets\ActivityAsset::register($this);

$this->registerJsConfig([
    'activity' => [
        'text' => [
            'activityEmpty' => Yii::t('ActivityModule.widgets_views_activityStream', 'There are no activities yet.')
        ]
    ]
]);

?>

<div class="panel panel-default panel-activities">
    <div class="panel-heading"><?= Yii::t('ActivityModule.widgets_views_activityStream', '<strong>Latest</strong> activities'); ?></div>
    <div id="activityStream" data-stream="<?= $streamUrl ?>">
        <ul id="activityContents" class="media-list activities" data-stream-content>
        </ul>
    </div>
</div>
<?php
/* @var $this humhub\components\View */

\humhub\modules\activity\assets\ActivityAsset::register($this);

//$this->registerJsFile('@web/resources/activity/activies.js');
//$this->registerJsVar('activityStreamUrl', $streamUrl);
$this->registerJsVar('activityInfoUrl', $infoUrl);

$this->registerJsConfig([
    'activity' => [
        'text' => [
            'activityEmpty' => Yii::t('ActivityModule.widgets_views_activityStream', 'There are no activities yet.')
        ]
    ]
]);

?>

<div class="panel panel-default panel-activities">
    <div class="panel-heading"><?php echo Yii::t('ActivityModule.widgets_views_activityStream', '<strong>Latest</strong> activities'); ?></div>
    <div id="activityStream" data-stream="<?= $streamUrl ?>">
        <ul id="activityContents" class="media-list activities" data-stream-content>
        </ul>
    </div>
</div>



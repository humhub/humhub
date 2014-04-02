<?php
/**
 * View File for the ActivityStreamWidget
 *
 * @uses CActiveDataProvider $dataProvider The data provider for this model
 * @uses User $model The user model
 *
 * @package humhub.modules.activity
 * @since 0.5
 */
?>

<div class="panel panel-default panel-activities">
    <div class="panel-heading"><?php echo Yii::t('ActivityModule.base', 'Latest activities'); ?></div>
    <div id="activityStream">
        <div id="activityEmpty" style="display:none">
            <div class="placeholder"><?php echo Yii::t('ActivityModule.base', 'There are no activities yet.'); ?></div>
        </div>
        <ul id="activityContents" class="media-list activities">
        </ul>
        <div class="loader" id="activityLoader"></div>
    </div>
</div>



<?php

use yii\helpers\Url;

/* @var $this humhub\components\View */
?>
<li>
    <!-- load modal confirm widget -->
    <?php
    echo humhub\widgets\ModalConfirm::widget(array(
        'uniqueID' => 'modal_postdelete_' . $id,
        'linkOutput' => 'a',
        'title' => Yii::t('ContentModule.widgets_views_deleteLink', '<strong>Confirm</strong> post deleting'),
        'message' => Yii::t('ContentModule.widgets_views_deleteLink', 'Do you really want to delete this post? All likes and comments will be lost!'),
        'buttonTrue' => Yii::t('ContentModule.widgets_views_deleteLink', 'Delete'),
        'buttonFalse' => Yii::t('ContentModule.widgets_views_deleteLink', 'Cancel'),
        'linkContent' => '<i class="fa fa-trash-o"></i> ' . Yii::t('ContentModule.widgets_views_deleteLink', 'Delete'),
        'linkHref' => Url::to(['/content/content/delete', 'model' => $model, 'id' => $id]),
        'confirmJS' => 'function(json) { $(".wall_"+json.uniqueId).remove(); }'
    ));
    ?>
</li>
<?php

/**
 * This view shows the delete link for wall entries.
 * Its used by DeleteLinkWidget.
 *
 * @property String $model the model name (e.g. Post)
 * @property String $id the primary key of the model (e.g. 1)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<li>
    <!-- load modal confirm widget -->
    <?php $this->widget('application.widgets.ModalConfirmWidget', array(
        'uniqueID' => 'modal_postdelete_'. $id,
        'linkOutput' => 'a',
        'title' => Yii::t('WallModule.widgets_views_deleteLink', '<strong>Confirm</strong> post deleting'),
        'message' => Yii::t('WallModule.widgets_views_deleteLink', 'Do you really want to delete this post? All likes and comments will be lost!'),
        'buttonTrue' => Yii::t('WallModule.widgets_views_deleteLink', 'Delete'),
        'buttonFalse' => Yii::t('WallModule.widgets_views_deleteLink', 'Cancel'),
        'linkContent' => '<i class="fa fa-trash-o"></i> ' . Yii::t('WallModule.widgets_views_deleteLink', 'Delete'),
        'linkHref' => $this->createUrl("//wall/content/delete", array('model' => $model, 'id' => $id)),
        'confirmJS' => 'function(jsonResp) { wallDelete(jsonResp); }'
    ));

    ?>
</li>
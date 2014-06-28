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
<li class="divider"></li>
<li>
    <?php
    //$label = '<i class="fa fa-trash-o"></i> '. Yii::t('base', 'Delete');

    //echo HHtml::ajaxLink($label, array('//wall/content/delete'), array('type'=>'post', 'data'=>array('model'=>$model, 'id'=>$id), 'success' => "function(jsonResp) { wallDelete(jsonResp); }"), array('id' => "deleteLink" . $model . "_" . $id));


    ?>

    <!-- load modal confirm widget -->
    <?php $this->widget('application.widgets.ModalConfirmWidget', array(
        'uniqueID' => 'modal_postdelete_'. $id,
        'linkOutput' => 'a',
        'title' => Yii::t('PostModule.base', '<strong>Confirm</strong> post deleting'),
        'message' => Yii::t('PostModule.base', 'Do you really want to delete this post? All likes and comments will be lost!'),
        'buttonTrue' => Yii::t('PostModule.base', 'Delete'),
        'buttonFalse' => Yii::t('PostModule.base', 'Cancel'),
        'linkContent' => '<i class="fa fa-trash-o"></i> ' . Yii::t('base', 'Delete'),
        'linkHref' => $this->createUrl("//wall/content/delete", array('model' => $model, 'id' => $id)),
        'confirmJS' => 'function(jsonResp) { wallDelete(jsonResp); }'
    ));

    ?>
</li>
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
$label = '<i class="icon-trash"></i> '. Yii::t('base', 'Delete');

echo HHtml::ajaxLink($label, array('//wall/content/delete'), array('type'=>'post', 'data'=>array('model'=>$model, 'id'=>$id), 'success' => "function(jsonResp) { wallDelete(jsonResp); }"), array('id' => "deleteLink" . $model . "_" . $id));


?>
</li>
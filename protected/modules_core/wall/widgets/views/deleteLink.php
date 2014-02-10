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

$url = CHtml::normalizeUrl(Yii::app()->createUrl('//wall/content/delete', array('model' => $model, 'id' => $id)));

echo HHtml::ajaxLink($label, $url, array('success' => "function(jsonResp) { wallDelete(jsonResp); }"), array('id' => "deleteLink" . $model . "_" . $id));
?>
</li>
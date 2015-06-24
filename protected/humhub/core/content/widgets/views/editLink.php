<?php
/**
 * This view shows the edit link for wall entries.
 * Its used by EditLinkWidget.
 *
 * @property string $id the primary key of the model (e.g. 1)
 * @property string $editRoute the route to the edit action
 * @property HActiveRecordContent $object the record which belongs the edit link to
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
?>
<li>
    <?php
    echo HHtml::ajaxLink('<i class="fa fa-pencil"></i> ' . Yii::t('WallModule.widgets_views_editLink', 'Edit'), Yii::app()->createUrl($editRoute, array('id' => $id)), array(
        'success' => "js:function(html){ $('.preferences .dropdown').removeClass('open'); $('#wall_content_" . $object->getUniqueId() . "').replaceWith(html); }"
    ));
    ?>
</li>

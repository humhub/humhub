<?php
/**
 * This view shows the edit link for wall entries.
 * Its used by EditLinkWidget.
 *
 * @property String $id the primary key of the model (e.g. 1)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
    <li>
        <?php echo HHtml::ajaxLink('<i class="fa fa-pencil"></i> Edit', Yii::app()->createAbsoluteUrl('//post/post/edit', array('id' => $id)), array(
            'success' => "js:function(html){ $('.preferences .dropdown').removeClass('open'); $('#post-content-" . $id . "').replaceWith(html); $('#post_input_". $id."_contenteditable').focus(); }"
        )); ?>
    </li>

<?php

/**
 * This view shows the permalink link for wall entries.
 * Its used by PermaLinkWidget.
 *
 * @property String $model the model name (e.g. Post)
 * @property String $id the primary key of the model (e.g. 1)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?><li>

    <?php $this->widget('application.widgets.ModalConfirmWidget', array(
        'uniqueID' => 'modal_permalink_'. $id,
        'linkOutput' => 'a',
        'title' => Yii::t('WallModule.widgets_views_permaLink', '<strong>Permalink</strong> to this post'),
        'message' => '<textarea rows="3" id="permalink-txt-'. $id .'" class="form-control permalink-txt">'. Yii::app()->createAbsoluteUrl('//wall/perma/content', array('model' => $model, 'id' => $id)) .'</textarea><p class="help-block">Copy to clipboard: Ctrl+C, Enter</p>',
        'buttonFalse' => Yii::t('WallModule.widgets_views_permaLink', 'Close'),
        'linkContent' => '<i class="fa fa-link"></i> ' . Yii::t('WallModule.widgets_views_permaLink', 'Permalink'),
        'linkHref' => '',
        'confirmJS' => 'function(jsonResp) { wallDelete(jsonResp); }',
        'modalShownJS' => 'setTimeout(function(){$("#permalink-txt-'. $id .'").focus(); $("#permalink-txt-'. $id .'").select();}, 1);'
    ));

    ?>
</li>

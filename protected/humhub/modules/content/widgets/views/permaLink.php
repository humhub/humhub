<?php


/* @var $this humhub\components\View */
?>
<li>

    <?php
    echo humhub\widgets\ModalConfirm::widget(array(
        'uniqueID' => 'modal_permalink_' . $id,
        'linkOutput' => 'a',
        'title' => Yii::t('ContentModule.widgets_views_permaLink', '<strong>Permalink</strong> to this post'),
        'message' => '<textarea rows="3" id="permalink-txt-' . $id . '" class="form-control permalink-txt">' . $permaLink . '</textarea><p class="help-block">Copy to clipboard: Ctrl+C, Enter</p>',
        'buttonFalse' => Yii::t('ContentModule.widgets_views_permaLink', 'Close'),
        'linkContent' => '<i class="fa fa-link"></i> ' . Yii::t('ContentModule.widgets_views_permaLink', 'Permalink'),
        'linkHref' => '',
        'modalShownJS' => 'setTimeout(function(){$("#permalink-txt-' . $id . '").focus(); $("#permalink-txt-' . $id . '").select();}, 1);'
    ));
    ?>
</li>

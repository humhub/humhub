<div id="lightbox_requestWorkspace">

    <div class="panel panel_lightbox">
        <div class="content content_innershadow">

            <h2><?php echo Yii::t('SpaceModule.widgets_views_requestMembershipSave', 'Request workspace membership'); ?></h2>

            <p>
                <?php echo Yii::t('SpaceModule.widgets_views_requestMembershipSave', 'Your request was successfully submitted to the workspace administrators.'); ?><br/>
            </p>
            <br/>
            <?php echo CHtml::link(Yii::t('SpaceModule.widgets_views_requestMembershipSave', 'Close'), '#', array('onclick'=>'redirect();//RequestWorkspacebox.close()', 'class' => 'button', 'style' => 'color: #fff;')); ?>
            <div class="clearFloats"></div>

        </div>
    </div>

</div>

<script type="text/javascript">

    jQuery('#close_button_requestWorkspace').remove();

    /**
     * Refresh the current page
     */
    function redirect() {
        window.location.href = "<?php Yii::app()->createUrl('workspace/publicShow', array('guid' => $workspace->guid)); ?>"  ;
    }

</script>   
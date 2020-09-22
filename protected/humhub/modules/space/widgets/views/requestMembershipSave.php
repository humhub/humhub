<?php
 use humhub\libs\Html;
?>
<div id="lightbox_requestWorkspace">

    <div class="panel panel_lightbox">
        <div class="content content_innershadow">

            <h2><?= Yii::t('SpaceModule.base', 'Request workspace membership'); ?></h2>

            <p><?= Yii::t('SpaceModule.base', 'Your request was successfully submitted to the workspace administrators.'); ?></p>
            <br><br>
            <?= CHtml::link(Yii::t('SpaceModule.base', 'Close'), '#', ['onclick'=>'redirect();//RequestWorkspacebox.close()', 'class' => 'button', 'style' => 'color: #fff;']); ?>

            <div class="clearFloats"></div>

        </div>
    </div>

</div>

<script <?= Html::nonce() ?>>

    $('#close_button_requestWorkspace').remove();

    /**
     * Refresh the current page
     */
    function redirect() {
        window.location.href = "<?php Yii::app()->createUrl('workspace/publicShow', ['guid' => $workspace->guid]); ?>";
    }

</script>   

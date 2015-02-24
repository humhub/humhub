<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('AdminModule.views_module_info', '<strong>Module</strong> details', array('%moduleName%' => CHtml::encode($name))); ?></h4>
        </div>
        <div class="modal-body">

            <div class="markdown-render">
                <?php if ($content != ""): ?>

                    <?php
                    $md = new CMarkdown;
                    echo $md->transform($content);
                    ?>

                <?php else: ?>
                    <?php echo $description; ?>
                    <br/>
                    <br/>

                    <?php echo Yii::t('AdminModule.views_module_info', 'This module doesn\'t provide further informations.'); ?>
                <?php endif; ?>
            </div>


        </div>

    </div>
</div>


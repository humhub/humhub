<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">

        
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('AdminModule.modules', 'More information: %moduleName%', array('%moduleName%' => $name)); ?></h4>
        </div>
        <div class="modal-body">
            
            <?php if ($content != ""): ?>
                <?php $this->beginWidget('CMarkdown'); ?>
                <?php echo $content; ?>
                <?php $this->endWidget(); ?>
            <?php else: ?>
                <?php echo $description; ?>
                <br />
                <br />
                
                <?php echo Yii::t('AdminModule.modules', 'This module doesn\'t provide further informations.'); ?>
            <?php endif; ?>
            
            
        </div>        
        
    </div>
</div>


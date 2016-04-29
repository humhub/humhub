<!-- start: Modal (every lightbox will/should use this construct to show content)-->
<div id="<?= $id ?>" <?= $modalData ?> class="modal" tabindex="-1" role="dialog" aria-labelledby="<?= $id ?>myModalLabel" aria-hidden="true">
    <div class="<?= $dialogClass ?>">
        <div class="modal-content">
            <?php if($header != null || $showClose): ?>
                <div class="modal-header">
                    <?php if($showClose): ?>
                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <?php endif;  ?>
                    <?php if($header != null): ?>
                        <h4 id="<?= $id ?>myModalLabel" class="modal-title"><?= $header ?></h4>
                    <?php endif;  ?>
                </div>
            <?php endif; ?>
            <div class="<?= $bodyClass ?>">
                <?php if($body != null): ?>
                    <?= $body ?>
                <?php endif; ?>
                <?php if($initialLoader): ?>
                    <?php echo \humhub\widgets\LoaderWidget::widget(); ?>
                <?php endif; ?>
            </div>
            <?php if($footer != null): ?>
                <div class="modal-footer">
                    <?= $footer ?> 
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php $this->widget('application.modules_core.admin.widgets.AdminMenuWidget', array()); ?>
        </div>
        <div class="col-md-9">
            <?php echo $content; ?>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php $this->widget('application.modules_core.admin.widgets.AdminMenuWidget', array()); ?>
        </div>
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Administration Interface
                </div>
                <div class="panel-body">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
</div>
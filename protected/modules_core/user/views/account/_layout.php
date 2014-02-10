<div class="container">
    <div class="row">
        <div class="col-md-2">
            <?php $this->widget('application.modules_core.user.widgets.AccountMenuWidget', array()); ?>
        </div>
        <div class="col-md-7">
            <div class="panel panel-default">
                <?php echo $content; ?>
            </div>
        </div>
        <div class="col-md-3">
            <img class="img-rounded" src="<?php echo Yii::app()->user->model->getProfileImage()->getUrl(); ?>"
                 data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
        </div>
    </div>
</div>

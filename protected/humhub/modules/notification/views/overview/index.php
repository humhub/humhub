<?php
use yii\widgets\ActiveForm;

?>
<div class="container">
    <div class="row">
        <div class="col-md-8 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.views_overview_index', 'Notification Overview'); ?>
                    <a id="notification_overview_markseen" href="#" class="pull-right heading-link" >
                        <b><?= Yii::t('NotificationModule.views_overview_index', 'Mark all as seen'); ?></b>
                    </a> 
                </div>
                <div class="panel-body">
                    <ul id="notification_overview_list" class="media-list">
                        <?php foreach($notificationEntries as $notification) :?>
                            <?=  $notification->getClass()->render(); ?>
                        <?php endforeach; ?>
                        <?php if(count($notificationEntries) == 0) :?>
                            <?= Yii::t('NotificationModule.views_overview_index', 'No Notification found!'); ?>
                        <?php endif; ?>
                    </ul>
                    <center>
                        <?= ($pagination != null) ? \humhub\widgets\LinkPager::widget(['pagination' => $pagination]) : ''; ?>
                    </center>
                </div>
            </div>
        </div>
        <div class="col-md-4 layout-sidebar-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Yii::t('NotificationModule.views_overview_index', 'Filter'); ?>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'notification_overview_filter']); ?>
                        <?php echo $form->field($filterForm, 'classFilter')->checkboxList($filterForm->getClassFilterSelection())->label(false); ?>
                        <button class="btn btn-info btn-xm" type="submit"><?= Yii::t('NotificationModule.views_overview_index', 'Filter'); ?></button>
                    <?php  ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type='text/javascript'>
    if(!$('#notification_overview_list li.new').length) {
        $('#notification_overview_markseen').hide();
    } else {
        $('#notification_overview_markseen').on('click', function(evt) {
            evt.preventDefault();
            $.ajax({
                'type': 'GET',
                'url': '<?php echo yii\helpers\Url::to(['/notification/list/mark-as-seen', 'ajax' => 1]); ?>',
                'success': function () {
                   location.reload();
                }
            });
        });
    }
</script>

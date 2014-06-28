<div class="panel-heading">
    <?php echo Yii::t('base', '<strong>User</strong> details new'); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>
</div>
<div class="panel-body">
    <?php echo $form; ?>
</div>
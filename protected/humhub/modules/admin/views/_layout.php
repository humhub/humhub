<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <!-- load space chooser widget -->
            <?php echo \humhub\modules\space\widgets\Chooser::widget(); ?>
        </div>
        <div class="col-lg-2">
            <?= \humhub\modules\admin\widgets\AdminMenu::widget(); ?>
        </div>
        <div class="col-lg-7">
            <?php echo $content; ?>
        </div>
    </div>
</div>
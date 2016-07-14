<div class="container">
    <div class="row">
        <div class="col-md-2">
            <?= humhub\modules\directory\widgets\Menu::widget(); ?>
        </div>
        <div class="col-md-7">
            <?php echo $content; ?>
        </div>
        <div class="col-md-3">
            <?php echo \humhub\modules\directory\widgets\Sidebar::widget(); ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-2">
            <?= \humhub\modules\directory\widgets\Menu::widget(); ?>
        </div>
        <div class="col-md-7">
            <?= $content; ?>
        </div>
        <div class="col-md-3">
            <?= \humhub\modules\directory\widgets\Sidebar::widget(); ?>
        </div>
    </div>
</div>

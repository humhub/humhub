<div class="container">
    <div class="row">
        <div class="col-md-2">
            <?= \humhub\modules\user\widgets\AccountMenu::widget(); ?>
        </div>
        <div class="col-md-7">
            <div class="panel panel-default">
                <?= $content; ?>
            </div>
        </div>
        <div class="col-md-3">
        </div>
    </div>
</div>
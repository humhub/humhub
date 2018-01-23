<div class="row space-content">
    <div class="col-md-8 layout-content-container">
        <?= $content ?>
    </div>
    <div class="col-md-4 layout-sidebar-container">
        <?=
        \humhub\modules\space\widgets\Sidebar::widget(['space' => $space, 'widgets' => [
                [\humhub\modules\activity\widgets\Stream::className(), ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 10]],
                [\humhub\modules\space\modules\manage\widgets\PendingApprovals::className(), ['space' => $space], ['sortOrder' => 20]],
                [\humhub\modules\space\widgets\Members::className(), ['space' => $space], ['sortOrder' => 30]]
        ]]);
        ?>
    </div>
</div>
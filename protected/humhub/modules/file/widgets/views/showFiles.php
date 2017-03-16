<?php

use humhub\modules\file\libs\FileHelper;


$object = $this->context->object;
?>

<?php if (count($files) > 0) : ?>
<!-- hideOnEdit mandatory since 1.2 -->
<div class="hideOnEdit">
    <!-- Show Images as Thumbnails -->
    <div class="post-files" id="post-files-<?php echo $object->getUniqueId(); ?>">
        <?php foreach ($files as $file): ?>
            <?php if ($previewImage->applyFile($file)): ?>
                <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>"  href="<?= $file->getUrl(); ?>#.jpeg">
                    <?= $previewImage->render(); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php $playlist = [] ?>
        
        <?php foreach ($files as $file): ?>
            <?php $fileExtension = FileHelper::getExtension($file->file_name); ?>
            <?php if ($fileExtension == "mp3") : ?>
                <?php $playlist[] = $file ?>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?= \humhub\widgets\JPlayerPlaylistWidget::widget([
            'playlist' => $playlist
        ]); ?>
        
    </div>

    <!-- Show List of all files -->
    <hr>
    <?= \humhub\modules\file\widgets\FilePreview::widget([
        'hideImageFileInfo' => $hideImageFileInfo,
        'model' => $object,
    ]);?>
    
</div>
<?php endif; ?>


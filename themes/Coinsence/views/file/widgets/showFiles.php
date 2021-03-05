<?php

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\widgets\FilePreview;
use humhub\widgets\JPlayerPlaylistWidget;
use yii\helpers\Html;

/* @var  $showPreview boolean */
/* @var  $files \humhub\modules\file\models\File[] */
/* @var  $previewImage \humhub\modules\file\converter\PreviewImage */
/* @var  $object \humhub\components\ActiveRecord */
/* @var  $hideImageFileInfo boolean */

?>

<?php if (count($files) > 0) : ?>
<!-- hideOnEdit mandatory since 1.2 -->
<div class="hideOnEdit">
    <!-- Show Images as Thumbnails -->
    <?php if($showPreview) :?>
    <?php $count=0 ?>
    <div class="post-files row" id="post-files-<?= $object->getUniqueId(); ?>">
        <?php foreach ($files as $file): ?>

            <?php if ($count<3): ?>
            <div class="col-md-4 col-6 imageContainer">
                <?php if ($count==2 && sizeof($files)-$count-1>0): ?>
                    <?php if ($previewImage->applyFile($file)): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" href="<?= $file->getUrl(); ?>#.jpeg"
                            title="<?= Html::encode($file->file_name) ?>">
                            <?= $previewImage->render(); ?>
                            <h2 class="moreImages">+<?php echo sizeof($files)-$count-1?> </h2>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'webm'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/webm"
                            href="<?= $file->getUrl(); ?>#.webm" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                            <h2 class="moreImages">+<?php echo sizeof($files)-$count-1?> </h2>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'mp4'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/mp4"
                            href="<?= $file->getUrl(); ?>#.mp4" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                            <h2 class="moreImages">+<?php echo sizeof($files)-$count-1?> </h2>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'ogv'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/ogg"
                            href="<?= $file->getUrl(); ?>#.ogv" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                            <h2 class="moreImages">+<?php echo sizeof($files)-$count-1?> </h2>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($previewImage->applyFile($file)): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" href="<?= $file->getUrl(); ?>#.jpeg"
                            title="<?= Html::encode($file->file_name) ?>">
                            <?= $previewImage->render(); ?>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'webm'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/webm"
                            href="<?= $file->getUrl(); ?>#.webm" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'mp4'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/mp4"
                            href="<?= $file->getUrl(); ?>#.mp4" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                        </a>
                    <?php elseif(FileHelper::getExtension($file->file_name) == 'ogv'): ?>
                        <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>" type="video/ogg"
                            href="<?= $file->getUrl(); ?>#.ogv" title="<?= Html::encode($file->file_name) ?>">
                            <video src="<?= $file->getUrl() ?>" preload="metadata" height="130"></video>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                    <?php $count++ ?>
            </div>
            <?php endif; ?>

        <?php endforeach; ?>
        <?php $playlist = [] ?>

        <?php foreach ($files as $file): ?>
            <?php if (FileHelper::getExtension($file->file_name) == "mp3") : ?>
            <?php $playlist[] = $file ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= JPlayerPlaylistWidget::widget([
                'playlist' => $playlist
            ]); ?>

    </div>
    <?php endif; ?>

</div>
<?php endif; ?>
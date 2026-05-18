<?php

use humhub\components\ActiveRecord;
use humhub\helpers\Html;
use humhub\helpers\ThemeHelper;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\widgets\FilePreview;
use humhub\widgets\JPlayerPlaylistWidget;

/* @var  $previewImage PreviewImage */
/* @var  $files \humhub\modules\file\models\File[] */
/* @var  $object ActiveRecord */
/* @var  $excludeMediaFilesPreview bool */
/* @var  $showPreview bool */

$videoExtensions = ['webm', 'mp4', 'ogv', 'mov'];
$images = [];
$videos = [];
$audios = [];

foreach ($files as $file) {
    if ($previewImage->applyFile($file)) {
        $images[] = $file;
    } elseif (in_array(FileHelper::getExtension($file->file_name), $videoExtensions, true)) {
        $videos[] = $file;
    } elseif (FileHelper::getExtension($file->file_name) === 'mp3') {
        $audios[] = $file;
    }
}

$nbFiles = count($files);
$isFluid = ThemeHelper::isFluid();

// Image height is defined in file.less and matches this template
$bsColumns = 6;
$bsColumnsMd = $isFluid ? 4 : 6;
$bsColumnsLg = $isFluid ? 3 : 4;
if ($nbFiles === 1) {
    $bsColumns = 12;
    $bsColumnsMd = $isFluid ? 6 : 12;
    $bsColumnsLg = $isFluid ? 4 : 6;
}
if ($nbFiles === 2) {
    $bsColumnsMd = 6;
    $bsColumnsLg = $isFluid ? 4 : 6;
}
if ($videos) {
    // When media contains videos, make the previews bigger
    $bsColumnsLg = $isFluid ? 4 : 6;
    if ($nbFiles === 1) {
        $bsColumnsLg = 12;
    }
}
$fullWidthColumnClass = 'col-media col-12';
$galleryColumnClass = 'col-media col-' . $bsColumns . ' col-lg-' . $bsColumnsMd . ' col-xl-' . $bsColumnsLg;

$videoTypes = [
    'webm' => 'video/webm',
    'mp4'  => 'video/mp4',
    'ogv'  => 'video/ogg',
    'mov'  => 'video/quicktime',
];
?>

<?php if ($nbFiles > 0): ?>
    <!-- hideOnEdit mandatory since 1.2 -->
    <div class="hideOnEdit">
        <!-- Show Medias as Thumbnails -->
        <?php if ($showPreview): ?>
            <div class="post-files d-flex flex-wrap justify-content-center" id="post-files-<?= $object->getUniqueId() ?>">
                <?php if (!empty($audios)): ?>
                    <div class="<?= $fullWidthColumnClass ?>">
                        <?= JPlayerPlaylistWidget::widget(['playlist' => $audios]) ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($videos as $video):
                    $ext = FileHelper::getExtension($video->file_name);
                    if (!isset($videoTypes[$ext])) {
                        continue;
                    } ?>
                    <div class="<?= $galleryColumnClass ?>">
                        <a data-ui-gallery="<?= 'gallery-' . $object->getUniqueId() ?>"
                           href="<?= $video->getUrl() ?>#.<?= $ext ?>"
                           title="<?= Html::encode($video->file_name) ?>"
                           class="d-flex align-items-center justify-content-center h-100 w-100"
                        >
                            <video src="<?= $video->getUrl() ?>#t=0.001"
                                   type="<?= $videoTypes[$ext] ?>"
                                   controls preload="metadata" height="130"></video>
                        </a>
                    </div>
                <?php endforeach ?>

                <?php foreach ($images as $image): ?>
                    <?php $previewImage->applyFile($image) ?>
                    <div class="<?= $galleryColumnClass ?>">
                        <a data-ui-gallery="<?= 'gallery-' . $object->getUniqueId(); ?>"
                           href="<?= $image->getUrl() ?>#.jpeg" title="<?= Html::encode($image->file_name) ?>">
                            <?= $previewImage->render() ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Show List of all files -->
        <?= FilePreview::widget([
            'excludeMediaFilesPreview' => $excludeMediaFilesPreview,
            'items' => $files,
            'model' => $object,
        ]) ?>
    </div>
<?php endif; ?>

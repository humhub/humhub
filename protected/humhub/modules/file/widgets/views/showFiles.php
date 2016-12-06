<?php

use yii\helpers\Html;
use humhub\libs\Helpers;

$object = $this->context->object;
?>

<?php if (count($files) != 0) : ?>

    <!-- Show Images as Thumbnails -->
    <div class="post-files" id="post-files-<?php echo $object->getUniqueId(); ?>">
        <?php foreach ($files as $file) : ?>
            <?php if ($file->getMimeBaseType() == "image") : ?>
                <?php
                //Note: We need to add "#.jpeg" to the full url for image  detection of ekko lightbox.
                ?>
                <a data-toggle="lightbox" data-gallery="<?php
                if (count($files) > 1) {
                    echo "gallery-" . $object->getUniqueId();
                }
                ?>" href="<?php echo $file->getUrl(); ?>#.jpeg"  data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                        <img src='<?= $file->getPreviewImageUrl($maxPreviewImageWidth ? $maxPreviewImageWidth : 200, $maxPreviewImageHeight ? $maxPreviewImageHeight : 200); ?>' style="max-height:200px;max-width:200px">
                </a>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>

    <!-- Show List of all files -->
    <hr>
    <ul class="files" style="list-style: none; margin: 0;" id="files-<?php echo $object->getPrimaryKey(); ?>">
        <?php foreach ($files as $file) : ?>
            <?php
            if ($file->getMimeBaseType() == "image" && $hideImageFileInfo)
                continue;
            ?>
            <li class="mime <?php echo \humhub\libs\MimeHelper::getMimeIconClassByExtension($file->getExtension()); ?>"><a
                    href="<?php echo $file->getUrl(); ?>" target="_blank"><span
                        class="filename"><?php echo Html::encode(Helpers::trimText($file->file_name, 40)); ?></span></a>
                <span class="time" style="padding-right: 20px;"> - <?php echo Yii::$app->formatter->asShortSize($file->size, $decimals = 1); ?></span>

                <?php if ($file->getExtension() == "mp3") : ?>
                    <!-- Integrate jPlayer -->
                    <?php
                    echo xj\jplayer\AudioWidget::widget(array(
                        'id' => $file->id,
                        'mediaOptions' => [
                            'mp3' => $file->getUrl(),
                        ],
                        'jsOptions' => [
                            'smoothPlayBar' => true,
                        ]
                    ));
                    ?>
                <?php endif; ?>

            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


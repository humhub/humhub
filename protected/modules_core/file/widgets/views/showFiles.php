<?php
/**
 * This view shows all attached files of a wall content object.
 *
 * @property Array $files a list of file objects
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 */
?>

<?php if (count($files) != 0) : ?>

    <!-- Show Images as Thumbnails -->
    <div class="post-files">
        <?php foreach ($files as $file) : ?>
            <?php if ($file->getMimeBaseType() == "image") : ?>
                <?php
                //Note: We need to add "#.jpeg" to the full url for image  detection of ekko lightbox.
                ?>
                <a data-toggle="lightbox" href="<?php echo $file->getUrl(); ?>#.jpeg"  data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                    <img src='<?php echo $file->getPreviewImageUrl(200, 200); ?>'>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Show List of all files -->
    <hr>
    <ul class="files" style="list-style: none; margin: 0;">
        <?php foreach ($files as $file) : ?>
            <li style="padding-left: 24px;" class="mime <?php echo HHtml::getMimeIconClassByExtension($file->getExtension()); ?>"><a
                    href="<?php echo $file->getUrl(); ?>" target="_blank"><span
                        class="filename"><?php echo $file->file_name; ?></span></a>
                <span class="time"> - <?php echo Yii::app()->format->formatSize($file->size); ?></span></li>
            <?php endforeach; ?>
    </ul>
<?php endif; ?>


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
    <div class="post-files" id="post-files-<?php echo $this->object->getUniqueId(); ?>">
        <?php foreach ($files as $file) : ?>
            <?php if ($file->getMimeBaseType() == "image") : ?>
                <?php
                //Note: We need to add "#.jpeg" to the full url for image  detection of ekko lightbox.
                ?>
                <a data-toggle="lightbox" data-gallery="<?php if (count($files) > 1) { echo "gallery-". $this->object->getUniqueId(); } ?>" href="<?php echo $file->getUrl(); ?>#.jpeg"  data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                    <img src='<?php echo $file->getPreviewImageUrl($maxPreviewImageWidth ? $maxPreviewImageWidth : 200, $maxPreviewImageHeight ? $maxPreviewImageHeight : 200); ?>'>
                </a>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>

    <!-- Show List of all files -->
    <hr>
    <ul class="files" style="list-style: none; margin: 0;" id="files-<?php echo $this->object->getPrimaryKey(); ?>">
        <?php foreach ($files as $file) : ?>
        	<?php if ($file->getMimeBaseType() == "image" && $hideImageFileInfo)
					continue;
        	?>
            <li class="mime <?php echo HHtml::getMimeIconClassByExtension($file->getExtension()); ?>"><a
                    href="<?php echo $file->getUrl(); ?>" target="_blank"><span
                        class="filename"><?php echo Helpers::trimText($file->file_name, 40); ?></span></a>
                <span class="time" style="padding-right: 20px;"> - <?php echo Yii::app()->format->formatSize($file->size); ?></span>

                <?php if ($file->getExtension() == "mp3") : ?>
                    <!-- Integrate jPlayer -->
                    <?php $this->widget('ext.jplayer.jplayer', array(
                        'id' => $file->id,
                        'file' => $file->getUrl(),
                    )); ?>
                <?php endif;?>

            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


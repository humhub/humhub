<?php

/**
 * This view shows the permalink link for wall entries.
 * Its used by PermaLinkWidget.
 *
 * @property String $model the model name (e.g. Post)
 * @property String $id the primary key of the model (e.g. 1)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?><li><a href="#" onClick="wallPermaLink('<?php echo $model; ?>', '<?php echo $id; ?>'); return false;"><i class="fa fa-link"></i> <?php echo Yii::t('WallModule.base', 'Permalink'); ?></a></li>

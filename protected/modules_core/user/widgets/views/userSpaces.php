<?php
/**
 * This View shows a list of all user spaces in sidebar.
 *
 * @property Array $spaces contains all spaces where the user is member. (Max. 30)
 *
 * @package humhub.modules_core.user
 * @since 0.5
 */
?>
<?php if (count($spaces) > 0) { ?>
    <div id="user-spaces-panel" class="panel panel-default members" style="position: relative;">

        <!-- Display panel menu widget -->
        <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'user-spaces-panel')); ?>

        <div class="panel-heading">
            <?php echo Yii::t('UserModule.widgets_views_userSpaces', '<strong>Member</strong> in these spaces'); ?>
        </div>

        <div class="panel-body">
            <?php foreach ($spaces as $space): ?>
                <a href="<?php echo Yii::app()->createUrl('//space/space', array('sguid' => $space->guid)); ?>">
                    <img src="<?php echo $space->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo CHtml::encode($space->name); ?></strong>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php } ?>
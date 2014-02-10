<?php
/**
 * Group page of directory
 *
 * @property Array $groups an array with group objects
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>
<div class="panel panel-default groups">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', 'Member Group Directory'); ?>
    </div>

    <div class="panel-body">
        <?php foreach ($groups as $group) : ?>
            <h3><?php echo $group->name; ?></h3>
            <?php $user_count = 0; ?>
            <?php foreach (User::model()->active()->findAllByAttributes(array('group_id' => $group->id), array('limit' => 30)) as $user) : ?>
                <a id="<?php echo $user->guid; ?>" href="<?php echo $user->getUrl(); ?>">
                    <img data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->title; ?>"
                         src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="40"
                         width="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"></a>
                <?php $user_count++; ?>
            <?php endforeach; ?>

            <?php if ($user_count >= 30) { ?>
                <?php echo HHtml::link(Yii::t('DirectoryModule.base', "show all members"), array('//directory/directory/members', 'keyword' => 'groupId:' . $group->id)); ?>
            <?php } ?>
            <hr>
        <?php endforeach; ?>
    </div>

</div>


<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\core\user\models\User;
?>
<div class="panel panel-default groups">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.views_directory_groups', '<strong>Member</strong> Group Directory'); ?>
    </div>

    <div class="panel-body">
        <?php foreach ($groups as $group) : ?>
            <?php $userCount = User::find()->where(['group_id' => $group->id])->count(); ?>

            <?php if ($userCount != 0) : ?>
                <h1><?php echo Html::encode($group->name); ?></h1>
                <?php foreach (User::find()->where(['group_id' => $group->id])->limit(30)->all() as $user) : ?>
                    <a id="<?php echo $user->guid; ?>" href="<?php echo $user->getUrl(); ?>">
                        <img data-toggle="tooltip" data-placement="top" title=""
                             data-original-title="<strong><?php echo Html::encode($user->displayName); ?></strong><br><?php echo Html::encode($user->profile->title); ?>"
                             src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                             height="40"
                             width="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"/></a>
                    <?php endforeach; ?>
                    <?php if ($userCount >= 30) : ?>
                        <?php echo Html::a(Yii::t('DirectoryModule.views_directory_groups', "show all members"), Url::to(['/directory/directory/members', 'keyword' => 'groupId:' . $group->id])); ?>
                    <?php endif; ?>
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

</div>


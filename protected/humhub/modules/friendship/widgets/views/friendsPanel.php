<?php

use yii\helpers\Html;

?>
<?php if (count($friends) > 0) { ?>
    <div class="card card-default follower" id="profile-friends-panel">
        <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'profile-friends-panel']); ?>

        <div class="card-header"><strong><?php echo Yii::t('FriendshipModule.base', 'Friends'); ?></strong> (<?php echo $totalCount; ?>)</div>

        <div class="card-body">
            <?php foreach ($friends as $friend): ?>
                <a href="<?php echo $friend->getUrl(); ?>">
                    <img src="<?php echo $friend->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-bs-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<?php echo Html::encode($friend->displayName); ?>">
                </a>
            <?php endforeach; ?>
            <?php if ($totalCount > $limit): ?>
                <br />
                <br />
                <?php echo Html::a(Yii::t('FriendshipModule.base', 'Show all friends'), ['/friendship/list/popup', 'userId' => $user->id], ['class' => 'btn btn-sm', 'data-target' => '#globalModal']); ?>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

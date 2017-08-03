<?php

use yii\helpers\Html;
?>

<?php if (count($friends) > 0) { ?>
    <div class="panel panel-default follower" id="profile-follower-panel">
        <?= \humhub\widgets\PanelMenu::widget(['id' => 'profile-friends-panel']); ?>

        <div class="panel-heading"><strong><?= Yii::t('FriendshipModule.base', 'Friends'); ?></strong> (<?= $totalCount; ?>)</div>

        <div class="panel-body">
            <?php foreach ($friends as $friend) : ?>
                <a href="<?= $friend->getUrl(); ?>">
                    <img src="<?= $friend->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<?= Html::encode($friend->displayName); ?>">
                </a>
            <?php endforeach; ?>
            <?php if ($totalCount > $limit) : ?>
                <br>
                <br>
                <?= Html::a(Yii::t('FriendshipModule.base', 'Show all friends'), ['/friendship/list/popup', 'userId' => $user->id], ['class' => 'btn btn-xs', 'data-target' => '#globalModal']); ?>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

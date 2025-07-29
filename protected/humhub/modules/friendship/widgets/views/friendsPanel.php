<?php

use humhub\helpers\Html;
use humhub\widgets\PanelMenu;

?>
<?php if (count($friends) > 0) { ?>
    <div class="panel panel-default follower" id="profile-friends-panel">
        <?= PanelMenu::widget() ?>

        <div class="panel-heading"><strong><?= Yii::t('FriendshipModule.base', 'Friends') ?></strong>
            (<?php echo $totalCount; ?>)
        </div>

        <div class="panel-body collapse">
            <?php foreach ($friends as $friend): ?>
                <a href="<?= $friend->getUrl() ?>">
                    <img src="<?= $friend->getProfileImage()->getUrl() ?>" class="rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-bs-toggle="tooltip" data-placement="top" title=""
                         data-bs-title="<?php echo Html::encode($friend->displayName); ?>">
                </a>
            <?php endforeach; ?>
            <?php if ($totalCount > $limit): ?>
                <br/>
                <br/>
                <?= Html::a(Yii::t('FriendshipModule.base', 'Show all friends'), ['/friendship/list/popup', 'userId' => $user->id], ['class' => 'btn btn-sm', 'data-bs-target' => '#globalModal']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

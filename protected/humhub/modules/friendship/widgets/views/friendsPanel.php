<?php

use humhub\helpers\Html;
use humhub\widgets\SidebarCard;

?>
<?php if (count($friends) > 0) { ?>
    <?php SidebarCard::begin([
        'title' => Yii::t('FriendshipModule.base', 'Friends') . ' (' . $totalCount . ')',
        'options' => ['id' => 'profile-friends-panel', 'class' => 'profile-friends-panel'],
    ]) ?>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($friends as $friend): ?>
                    <a href="<?= $friend->getUrl() ?>">
                        <img src="<?= $friend->image ?>" class="rounded tt img_margin"
                             height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                             style="width: 24px; height: 24px;"
                             data-bs-toggle="tooltip" data-placement="top" title=""
                             data-bs-title="<?php echo Html::encode($friend->displayName); ?>">
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($totalCount > $limit): ?>
                <?= Html::a(Yii::t('FriendshipModule.base', 'Show all friends'), ['/friendship/list/popup', 'userId' => $user->id], ['class' => 'btn btn-sm mt-2', 'data-bs-target' => '#globalModal']) ?>
            <?php endif; ?>
    <?php SidebarCard::end() ?>
<?php } ?>

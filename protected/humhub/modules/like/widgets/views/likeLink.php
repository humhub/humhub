<?php

use yii\helpers\Html;

$this->registerJsFile('@web/resources/like/like.js', ['position' => humhub\components\View::POS_BEGIN]);
?>

<span class="likeLinkContainer" id="likeLinkContainer_<?= $id ?>">

    <?php if (Yii::$app->user->isGuest): ?>
        <?php echo Html::a(Yii::t('LikeModule.widgets_views_likeLink', 'Like'), Yii::$app->user->loginUrl, array('data-target' => '#globalModal', 'data-toggle' => 'modal')); ?>
    <?php else: ?>
        <?php echo Html::a(Yii::t('LikeModule.widgets_views_likeLink', 'Like'), $likeUrl, ['style' => 'display:' . ((!$currentUserLiked) ? 'inline' : 'none'), 'class' => 'like likeAnchor', 'data-objectId' => $id]); ?>
        <?php echo Html::a(Yii::t('LikeModule.widgets_views_likeLink', 'Unlike'), $unlikeUrl, ['style' => 'display:' . (($currentUserLiked) ? 'inline' : 'none'), 'class' => 'unlike likeAnchor', 'data-objectId' => $id]); ?>
    <?php endif; ?>

<?php if (count($likes) > 0) { ?>
        <!-- Create link to show all users, who liked this -->
        <a href="<?php echo $userListUrl; ?>"
           class="tt" data-toggle="modal"
           data-placement="top" title="" data-target="#globalModal"
           data-original-title="<?= $title ?>"><span class="likeCount"></span></a>
    <?php } else { ?>
        <span class="likeCount"></span>
<?php } ?>

</span>

<script>
    $(function () {
        updateLikeCounters($("#likeLinkContainer_<?= $id ?>"), <?= count($likes); ?>);
        initLikeModule();
    });
</script>
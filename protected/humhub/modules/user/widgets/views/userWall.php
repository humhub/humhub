<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel panel-default" id="user-wall-<?= $user->id; ?>">
    <div class="panel-body">

        <div class="media">

            <span class="label label-default pull-right"><?= Yii::t('UserModule.widgets_views_userWall', 'User'); ?></span>

            <a href="<?= $user->getUrl(); ?>" class="pull-left">
                <img class="media-object img-rounded user-image user-<?= $user->guid; ?>" alt="40x40"
                     data-src="holder.js/40x40" style="width: 40px; height: 40px;"
                     src="<?= $user->getProfileImage()->getUrl(); ?>"
                     width="40" height="40"/>
            </a>

            <div class="media-body">
                <!-- show username with link and creation time-->
                <h4 class="media-heading">
                    <a href="<?= $user->getUrl(); ?>"><?= Html::encode($user->displayName); ?></a>
                </h4>
                <h5><?= Html::encode($user->profile->title); ?></h5>
                <!-- start: tags for user skills -->
                <?php $tag_count = 0; ?>
                <?php if ($user->hasTags()) : ?>
                    <?php foreach ($user->getTags() as $tag): ?>
                        <?php if ($tag_count <= 5) { ?>
                            <?= Html::a(Html::encode($tag), Url::to(['/directory/directory/members', 'keyword' =>  $tag]), array('class' => 'label label-default')); ?>
                            <?php
                            $tag_count++;
                        }
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- end: tags for user skills -->
            </div>
        </div>

    </div>
</div>

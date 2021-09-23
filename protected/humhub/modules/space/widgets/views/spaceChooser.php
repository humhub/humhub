<?php

use humhub\modules\space\assets\SpaceChooserAsset;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpaceChooserItem;
use humhub\modules\space\widgets\Image;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use yii\helpers\Url;


/* @var $this View */
/* @var $currentSpace Space */
/* @var $memberships Membership[] */
/* @var $followSpaces Space[] */
/* @var $canCreateSpace boolean */
/* @var $canAccessDirectory boolean */

SpaceChooserAsset::register($this);

$noSpaceView = '<div class="no-space"><i class="far fa-dot-circle"></i><br>' . Yii::t('SpaceModule.chooser', 'My spaces') . '<b class="caret"></b></div>';

$this->registerJsConfig('space.chooser', [
    'noSpace' => $noSpaceView,
    'remoteSearchUrl' =>  Url::to(['/space/browse/search-json']),
    'text' => [
        'info.remoteAtLeastInput' => Yii::t('SpaceModule.chooser', 'To search for other spaces, type at least {count} characters.', ['count' => 2]),
        'info.emptyOwnResult' => Yii::t('SpaceModule.chooser', 'No member or following spaces found.'),
        'info.emptyResult' => Yii::t('SpaceModule.chooser', 'No result found for the given filter.'),
    ],
]);
?>

<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if ($currentSpace) : ?>
            <?= Image::widget([
                'space' => $currentSpace,
                'width' => 32,
                'htmlOptions' => [
                    'class' => 'current-space-image',
                ]
            ]);
            ?>
            <b class="caret"></b>
        <?php endif; ?>

        <?php if (!$currentSpace) : ?>
            <?= $noSpaceView ?>
        <?php endif; ?>
        <!-- end: Show space image and name if chosen -->
    </a>

    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls">
                <div <?php if($canAccessDirectory) : ?>class="input-group"<?php endif; ?>>
                    <input type="text" id="space-menu-search" class="form-control" autocomplete="off"
                           placeholder="<?= Yii::t('SpaceModule.chooser', 'Search') ?>"
                           title="<?= Yii::t('SpaceModule.chooser', 'Search for spaces') ?>">
                    <?php if($canAccessDirectory) : ?>
                        <span id="space-directory-link" class="input-group-addon" >
                            <a href="<?= Url::to(['/space/spaces']) ?>">
                                <?= Icon::get('directory')?>
                            </a>
                        </span>
                    <?php endif; ?>
                    <div class="search-reset" id="space-search-reset"><?= Icon::get('times-circle') ?></div>
                </div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list notLoaded" id="space-menu-spaces">
                <?php foreach ($memberships as $membership) : ?>
                    <?= SpaceChooserItem::widget([
                        'space' => $membership->space,
                        'updateCount' => $membership->countNewItems(),
                        'isMember' => true
                    ]);
                    ?>
                <?php endforeach; ?>
                <?php foreach ($followSpaces as $followSpace) : ?>
                    <?= SpaceChooserItem::widget([
                        'space' => $followSpace,
                        'isFollowing' => true
                    ]);
                    ?>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="remoteSearch">
            <ul id="space-menu-remote-search" class="media-list notLoaded"></ul>
        </li>

    <?php if ($canCreateSpace) : ?>
        <li>
            <div class="dropdown-footer">
                <a href="#" class="btn btn-info col-md-12" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/space/create/create']) ?>">
                    <?= Yii::t('SpaceModule.chooser', 'Create new space') ?>
                </a>
            </div>
        </li>
    <?php endif; ?>
    </ul>
</li>

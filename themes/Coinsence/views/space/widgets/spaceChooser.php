<?php

/* @var $this \humhub\components\WebView */
/* @var $currentSpace \humhub\modules\space\models\Space */

use humhub\modules\space\assets\SpaceChooserAsset;
use humhub\modules\space\widgets\SpaceChooserItem;
use humhub\modules\space\widgets\Image;
use yii\helpers\Url;
use yii\helpers\Html;

SpaceChooserAsset::register($this);

$noSpaceView = '<div class="no-space"><i class="fa fa-dot-circle-o"></i><b class="caret"></b></div>';

$this->registerJsConfig('space.chooser', [
    'noSpace' => $noSpaceView,
    'remoteSearchUrl' =>  Url::to(['/space/browse/search-json']),
    'text' => [
        'info.remoteAtLeastInput' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'To search for other spaces, type at least {count} characters.', ['count' => 2]),
        'info.emptyOwnResult' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'No member or following spaces found.'),
        'info.emptyResult' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'No result found for the given filter.'),
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
            <span><?= $currentSpace->name ?></span>
            <b class="caret"></b>
        <?php endif; ?>

        <?php if (!$currentSpace) : ?>
            <?= $noSpaceView ?>
        <?php endif; ?>
        <!-- end: Show space image and name if chosen -->
    </a>

    <ul class="dropdown-menu" id="space-menu-dropdown">
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

    </ul>
    <script>
        if (humhub.modules.event) {
            const event = humhub.modules.event;
            event.on('humhub:modules:space:chooser:afterInit', function(evt, spaceChooser) {
                console.log('humhub:modules:space:chooser:afterInit');
                /**
                 * Changes the space chooser icon, for the given space options.
                 * 
                 * @param {type} spaceOptions
                 * @returns {undefined}
                 */
                spaceChooser.SpaceChooser.prototype.setSpace = function (space) {
                    this.setSpaceMessageCount(space, 0);
                    this._changeMenuButton(space.image + '<span>' + space.name + '</span>' + ' <b class="caret"></b>');
                };
                
            });
        }
    </script>
</li>


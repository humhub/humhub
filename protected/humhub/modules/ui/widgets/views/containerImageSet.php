<?php

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\widgets\Image as UserImage;
use yii\helpers\Html;

humhub\modules\ui\assets\UiImageSetAsset::register($this);
?>
<div class="ui-imageset-wrapper">
    <div class="ui-imageset-items" style="padding-right: <?= $options['width'] ?>px">
        <?php
        foreach ($visibleItems as $item) {
            if ($item instanceof Space) {
                echo SpaceImage::widget(array_merge($options, ['space' => $item]));
            } else if ($item instanceof User) {
                echo UserImage::widget(array_merge($options, ['user' => $item]));
            }
        }
        ?>
    </div>

    <?php
        if (count($hiddenItems) > 0) {
            $top = $options['height'] * 0.5 - 10;
            $right = $options['width'] * 0.5 - 10;
    ?>
            <span class="ui-imageset-show-more tt" style="top: <?= $top ?>px; right: <?= $right ?>px;" data-toggle="tooltip" data-placement="top" data-original-title="Show more">+</span>

            <div class="ui-imageset-hidden-items">
            <?php
            foreach ($hiddenItems as $item) {
                ?>
                <div>
                    <?php
                    if ($item instanceof Space) {
                        echo SpaceImage::widget(array_merge($options, [
                            'space' => $item,
                            'width' => $hiddenItemsOptions['width'],
                            'height' => $hiddenItemsOptions['height'],
                            'showTooltip' => false
                        ]));
                    } else if ($item instanceof User) {
                        echo UserImage::widget(array_merge($options, [
                            'user' => $item,
                            'width' => $hiddenItemsOptions['width'],
                            'height' => $hiddenItemsOptions['height'],
                            'showTooltip' => false
                        ]));
                    }
                    $itemDisplayName = ' - ' . $item->getDisplayName();
                    if ($options['link']) {
                        echo Html::a($itemDisplayName, $item->getUrl(), $options['linkOptions']);
                    } else {
                        echo Html::tag('span', $itemDisplayName, $options['htmlOptions']);
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
    <?php
        }
    ?>
</div>

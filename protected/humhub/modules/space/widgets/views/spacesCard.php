<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\modules\space\widgets\SpacesIcons;
use humhub\modules\space\widgets\SpacesTagList;
use yii\web\View;

/* @var $this View */
/* @var $space Space */
?>

<div class="card-panel">
    <div class="card-bg-image"<?php if ($space->getProfileBannerImage()->hasImage()) : ?> style="background-image: url('<?= $space->getProfileBannerImage()->getUrl() ?>')"<?php endif; ?>></div>
    <div class="card-header">
        <?= Image::widget([
            'space' => $space,
            'link' => true,
            'linkOptions' => ['data-contentcontainer-id' => $space->contentcontainer_id, 'class' => 'card-image-link'],
            'width' => 100,
        ]); ?>
    </div>
    <div class="card-body">
        <h4><?= Html::containerLink($space); ?></h4>
        <?php if (trim($space->description) !== '') : ?>
            <h5><?= Html::encode($space->description); ?></h5>
        <?php endif; ?>
        <?= SpacesTagList::widget(['space' => $space]); ?>
    </div>
    <div class="card-footer card-icons">
        <?= SpacesIcons::widget(['space' => $space]); ?>
    </div>
</div>
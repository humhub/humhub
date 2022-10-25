<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\content\assets\ContentFormAsset;

/* @var $contentContainer ContentContainerActiveRecord */
/* @var $formClass string */

ContentFormAsset::register($this);
?>

<?= WallCreateContentMenu::widget(['contentContainer' => $contentContainer]) ?>

<?php if ($formClass) : ?>
    <?= $formClass::widget(['contentContainer' => $contentContainer]) ?>
<?php endif; ?>
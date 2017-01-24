<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\helpers\Html;

/* @var $container \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $url string */

?>

<a href="<?= $url ?>">
    <img src="<?= $container->getProfileImage()->getUrl("", true); ?>"
         width="50"
         height="50"
         alt=""
         title="<?= Html::encode($container->displayName) ?>"
         style="border-radius: 4px;"
         border="0" hspace="0" vspace="0"/>
</a>
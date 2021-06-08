<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\models\Space;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $space Space */
?>

<a href="#" class="fa fa-users" data-action-click="ui.modal.load" data-action-url="<?= Url::to(['/space/membership/members-list', 'container' => $space]) ?>"> <span><?= Yii::$app->formatter->asShortInteger($space->getMemberships()->count()) ?></span></a>
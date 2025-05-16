<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\topic\widgets\TopicPicker;

/* @var $this View */
/* @var $title string */

?>

<?= Html::beginTag('div', $options) ?>
<strong><?= $title ?></strong>
<?= TopicPicker::widget([
    'id' => 'stream_filter_topic',
    'name' => 'filter_topic'
]); ?>
<?= Html::endTag('div') ?>

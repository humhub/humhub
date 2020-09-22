<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\libs\Html;
use humhub\modules\topic\widgets\TopicPicker;

/* @var $this \humhub\components\View */
/* @var $title string */

?>

<?= Html::beginTag('div', $options) ?>
    <strong><?= $title ?></strong>
    <?= TopicPicker::widget([
        'id' => 'stream_filter_topic',
        'name' => 'filter_topic'
    ]); ?>
<?= Html::endTag('div') ?>

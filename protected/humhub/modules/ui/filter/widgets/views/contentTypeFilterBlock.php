<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\widgets\ContentTypePicker;

/* @var $this View */
/* @var $title string */
?>

<?= Html::beginTag('div', $options) ?>
<strong><?= $title ?></strong>
<?= ContentTypePicker::widget([
    'id' => 'stream_filter_content_type',
    'name' => 'filter_content_type'
]); ?>
<?= Html::endTag('div') ?>

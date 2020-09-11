<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\libs\Html;
use humhub\modules\content\widgets\ContentTypePicker;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $title string */
?>

<?= Html::beginTag('div', $options) ?>
    <strong><?= $title ?></strong>
    <?= ContentTypePicker::widget([
        'id' => 'stream_filter_content_type',
        'name' => 'filter_content_type'
    ]); ?>
<?= Html::endTag('div') ?>

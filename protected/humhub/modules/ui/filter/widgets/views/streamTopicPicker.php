<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this \humhub\components\View */

use humhub\modules\topic\widgets\TopicPicker;

?>

<?= TopicPicker::widget([
    'id' => 'stream-topic-picker',
    'name' => 'stream-topic-picker',
    'addOptions' => false
])
?>

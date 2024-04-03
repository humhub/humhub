<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this View */

use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\ui\view\components\View;

?>

<?= TopicPicker::widget([
    'id' => 'stream-topic-picker',
    'name' => 'stream-topic-picker',
    'addOptions' => false
])
?>

<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this View */
/* @var $pickerClass string */
/* @var $pickerOptions string */

/* @var $options array */

use humhub\modules\ui\view\components\View;

?>
<?= call_user_func($pickerClass . '::widget', $pickerOptions) ?>

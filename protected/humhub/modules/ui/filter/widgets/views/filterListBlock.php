<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\libs\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $title string */
/* @var $filters array */
/* @var $options array */

?>

<?= Html::beginTag('div', $options) ?>
    <strong><?= $title ?></strong>
    <ul class="filter-list">

        <?php foreach ($filters as $filter): ?>
            <li>
                <?= call_user_func($filter['class'].'::widget', $filter) ?>
            </li>
        <?php endforeach; ?>

    </ul>
<?= Html::endTag('div') ?>

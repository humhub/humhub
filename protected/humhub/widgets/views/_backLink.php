<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\widgets\Icon;

/**
 * The back arrow before a page or card title: a ghost icon button, named by its label (tooltip
 * and `aria-label`).
 *
 * @var string|array $url
 * @var string|null $label e.g. "Back to overview"; "Back" without
 * @var string $class extra classes, e.g. `page-header-back`
 */
$label ??= Yii::t('base', 'Back');
?>
<?= Html::a(Icon::get('arrow-left'), $url, [
    'class' => trim(($class ?? '') . ' btn btn-ghost btn-icon-only'),
    'aria-label' => $label,
    'title' => $label,
]) ?>

<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Link;

/* @var $space Space */
/* @var $membersCount int */

$text = Icon::get('users') . '<span>' . $membersCount . '</span>';
$ariaLabel = Yii::t('SpaceModule.base', '{count} Members', ['count' => $membersCount]);
?>
<?php if ($space->canViewMembers()) : ?>
    <?= Link::withAction($text, 'ui.modal.load', $space->createUrl('/space/membership/members-list'))
        ->encodeLabel(false)
        ->options(['aria-label' => $ariaLabel]) ?>
<?php else: ?>
    <?= Html::tag('span', $text, ['aria-label' => $ariaLabel]) ?>
<?php endif; ?>

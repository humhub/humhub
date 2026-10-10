<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\services\NotificationBlockRenderer;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\notification\targets\MailTarget;
use yii\helpers\Url;

/**
 * The plaintext notification mail of a {@see DeliveryBatch}, inside the global text mail layout.
 *
 * One notification: its sentence and its blocks ({@see NotificationBlockRenderer}), buttons as
 * `label: URL` lines, separated by empty lines. Several: the subject as headline, per
 * notification its sentence and, on the next line, its entry URL, and the overview URL at the end.
 */

/* @var View $this */
/* @var DeliveryBatch $batch */

$lines = [];
if ($batch->isSingle()) {
    $notification = $batch->first();
    $context = new NotificationContext(MailTarget::ID);
    $renderer = new NotificationBlockRenderer($notification);

    $lines[] = $notification->asMailText();
    $lines[] = '';
    foreach ($renderer->renderMailText($notification->getRenderedBlocks($context)) as $part) {
        $lines[] = $part;
        $lines[] = '';
    }
} else {
    $lines[] = $batch->getSubject();
    $lines[] = '';
    foreach ($batch->notifications as $notification) {
        $lines[] = $notification->asMailText();
        $lines[] = $notification->getEntryUrl();
        $lines[] = '';
    }
    $lines[] = Yii::t('NotificationModule.base', 'Open notifications') . ': ' . Url::to(['/notification/overview'], true);
}

echo rtrim(implode("\n", $lines)) . "\n";

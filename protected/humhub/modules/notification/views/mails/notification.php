<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\message\MessageFormat;
use humhub\components\View;
use humhub\helpers\Html;
use humhub\helpers\MailStyleHelper;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationAction;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\space\models\Space;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailContentEntry;
use humhub\widgets\mails\MailHeadline;
use yii\helpers\Url;

/**
 * The notification mail of a {@see DeliveryBatch}, inside the global mail layout.
 *
 * One notification: its sentence, its excerpt, the preview of its preview record and its actions
 * as buttons. Several: a headline, the sentences (with their excerpt) and a "View" link each -
 * those without a space first, the others under the heading of their space - and a button to the
 * overview.
 */

/* @var View $this */
/* @var DeliveryBatch $batch */

$spaceOf = static fn(BaseNotification $notification): ?Space => $notification->getSpace();

$textStyle = 'font-size: 14px; line-height: 22px; font-family: ' . MailStyleHelper::getFontFamily()
    . '; color: ' . MailStyleHelper::getTextColorMain() . '; font-weight: 300; text-align: left;';
$linkStyle = 'text-decoration: none; color: ' . MailStyleHelper::getColorPrimary() . '; font-weight: bold;';
?>
<!-- START NOTIFICATION -->
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>; border-radius: 0 0 4px 4px">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>">

                        <?php if ($batch->isSingle()): ?>
                            <?php $notification = $batch->first() ?>
                            <tr>
                                <td valign="top" align="left" style="<?= $textStyle ?>">
                                    <?= $notification->asMailHtml() ?>
                                </td>
                            </tr>
                            <tr>
                                <td height="15"></td>
                            </tr>

                            <?php if (($excerpt = $notification->renderExcerpt(MessageFormat::Html)) !== null): ?>
                                <tr>
                                    <td valign="top" align="left" style="<?= $textStyle ?>">
                                        <p style="margin: 0"><?= $excerpt ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="15"></td>
                                </tr>
                            <?php endif ?>

                            <?php if ($record = $notification->getPreviewRecord()): ?>
                                <tr>
                                    <td valign="top" align="left">
                                        <?= MailContentEntry::widget([
                                            'content' => $record,
                                            'originator' => $notification->originator,
                                            'receiver' => $batch->recipient,
                                            'space' => $spaceOf($notification),
                                            'date' => $notification->record->created_at,
                                        ]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="15"></td>
                                </tr>
                            <?php endif ?>

                            <?php if ($actions = $notification->getActions()): ?>
                                <tr>
                                    <td valign="top">
                                        <?= MailButtonList::widget(['buttons' => array_map(
                                            static fn(NotificationAction $action): string => MailButton::widget([
                                                'url' => Html::encode($action->url),
                                                'text' => Html::encode($action->label),
                                            ]),
                                            $actions,
                                        )]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="15"></td>
                                </tr>
                            <?php endif ?>
                        <?php else: ?>
                            <?php
                            // Those without a space first, then per space in the order of their first notification
                            $sections = ['' => ['space' => null, 'notifications' => []]];
                            foreach ($batch->notifications as $notification) {
                                $space = $spaceOf($notification);
                                $key = $space ? (string)$space->id : '';
                                $sections[$key] ??= ['space' => $space, 'notifications' => []];
                                $sections[$key]['notifications'][] = $notification;
                            }
                            $sections = array_filter($sections, static fn(array $section): bool => $section['notifications'] !== []);
                            ?>
                            <tr>
                                <td valign="top">
                                    <?= MailHeadline::widget(['text' => Html::encode($batch->getSubject())]) ?>
                                </td>
                            </tr>

                            <?php foreach ($sections as $section): ?>
                                <?php if ($section['space'] !== null): ?>
                                    <tr>
                                        <td valign="top">
                                            <?= MailHeadline::widget([
                                                'text' => Html::encode($section['space']->displayName),
                                                'level' => 2,
                                            ]) ?>
                                        </td>
                                    </tr>
                                <?php endif ?>
                                <?php foreach ($section['notifications'] as $notification): ?>
                                    <tr>
                                        <td valign="top" align="left" style="<?= $textStyle ?>">
                                            <?= $notification->asMailHtml() ?>
                                            - <a href="<?= Html::encode($notification->getEntryUrl()) ?>"
                                                 style="<?= $linkStyle ?>"><?= Yii::t('NotificationModule.base', 'View') ?></a>
                                            <?php if (($excerpt = $notification->renderExcerpt(MessageFormat::Html)) !== null): ?>
                                                <p style="margin: 5px 0 0"><?= $excerpt ?></p>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10"></td>
                                    </tr>
                                <?php endforeach ?>
                                <tr>
                                    <td height="10"></td>
                                </tr>
                            <?php endforeach ?>

                            <tr>
                                <td valign="top">
                                    <?= MailButtonList::widget(['buttons' => [
                                        MailButton::widget([
                                            'url' => Html::encode(Url::to(['/notification/overview'], true)),
                                            'text' => Yii::t('NotificationModule.base', 'Open notifications'),
                                        ]),
                                    ]]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td height="15"></td>
                            </tr>
                        <?php endif ?>

                    </table>
                    <!-- end container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end container width 600px -->
    </td>
</tr>
<!-- END NOTIFICATION -->

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\helpers\Html;
use humhub\helpers\MailStyleHelper;
use humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailCommentEntry;
use humhub\widgets\mails\MailContentEntry;

/**
 * Renders the {@see NotificationBlock}s of a notification for a mail - as mail-safe HTML (tables,
 * inline styles via {@see MailStyleHelper}) or as plain text. Used by the notification mail views;
 * the recipient's language is set by the caller ({@see \humhub\modules\notification\targets\MailTarget}).
 *
 * Consecutive buttons form one part: a row of buttons, or a `label: URL` line each. The mail
 * shows a block in full; a future output with less room shortens it here.
 *
 * @internal
 * @since 1.20
 */
final readonly class NotificationBlockRenderer
{
    /**
     * The width of the mail's content column, the limit for images of a RichText block.
     */
    private const MAIL_WIDTH = 560;

    public function __construct(private BaseNotification $notification)
    {
    }

    /**
     * The blocks as HTML, one part per cell of the mail's content table.
     *
     * @param NotificationBlock[] $blocks
     * @return string[]
     */
    public function renderMailHtml(array $blocks): array
    {
        return array_map(
            fn(array $part): string => $part[0]->getType() === NotificationBlock::TYPE_BUTTON
                ? MailButtonList::widget(['buttons' => array_map(
                    static fn(NotificationBlock $button): string => MailButton::widget([
                        'url' => Html::encode($button->getUrl()),
                        'text' => Html::encode($button->getText()),
                    ]),
                    $part,
                )])
                : $this->renderBlockHtml($part[0]),
            $this->parts($blocks),
        );
    }

    /**
     * The blocks as plain text, one part per paragraph, without trailing line breaks; empty parts
     * left out.
     *
     * @param NotificationBlock[] $blocks
     * @return string[]
     */
    public function renderMailText(array $blocks): array
    {
        $parts = array_map(
            fn(array $part): string => $part[0]->getType() === NotificationBlock::TYPE_BUTTON
                ? implode("\n", array_map(
                    static fn(NotificationBlock $button): string => trim($button->getText()) . ': ' . $button->getUrl(),
                    $part,
                ))
                : $this->renderBlockText($part[0]),
            $this->parts($blocks),
        );

        return array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));
    }

    /**
     * The blocks in parts: a run of consecutive buttons, or a single other block.
     *
     * @param NotificationBlock[] $blocks
     * @return NotificationBlock[][]
     */
    private function parts(array $blocks): array
    {
        $parts = [];
        $previous = null;
        foreach ($blocks as $block) {
            $isButton = $block->getType() === NotificationBlock::TYPE_BUTTON;
            if ($isButton && $previous === NotificationBlock::TYPE_BUTTON) {
                $parts[count($parts) - 1][] = $block;
            } else {
                $parts[] = [$block];
            }
            $previous = $block->getType();
        }

        return $parts;
    }

    private function renderBlockHtml(NotificationBlock $block): string
    {
        return match ($block->getType()) {
            NotificationBlock::TYPE_HEADING => Html::tag('p', Html::encode(trim($block->getText())), [
                'style' => $this->textStyle(16, MailStyleHelper::getTextColorHighlight(), 'bold'),
            ]),
            NotificationBlock::TYPE_TEXT => Html::tag('p', $this->encodeLines($block->getText()), [
                'style' => $this->textStyle(14, MailStyleHelper::getTextColorMain(), '300'),
            ]),
            NotificationBlock::TYPE_RICH_TEXT => $this->renderRichTextHtml($block->getText()),
            NotificationBlock::TYPE_QUOTE => $this->renderQuoteHtml($block),
            NotificationBlock::TYPE_CONTENT_PREVIEW => MailContentEntry::widget([
                'content' => $block->getRecord(),
                'originator' => $this->notification->originator,
                'receiver' => $this->notification->recipient,
                'space' => $this->notification->getSpace(),
                'date' => $this->notification->record->created_at,
            ]),
            NotificationBlock::TYPE_HTML => (string)$block->getHtml(),
        };
    }

    private function renderBlockText(NotificationBlock $block): string
    {
        return match ($block->getType()) {
            NotificationBlock::TYPE_HEADING,
            NotificationBlock::TYPE_TEXT,
            NotificationBlock::TYPE_HTML => trim($block->getText()),
            NotificationBlock::TYPE_RICH_TEXT => $this->toPlainText($block->getText()),
            NotificationBlock::TYPE_QUOTE => $this->renderQuoteText($block),
            NotificationBlock::TYPE_CONTENT_PREVIEW => $this->toPlainText(
                (string)$block->getRecord()?->getContentDescription(),
            ),
        };
    }

    private function renderRichTextHtml(string $richText): string
    {
        // the converter limits images to their container (`max-width: 100%`); the fixed table
        // keeps the container at the mail's width in clients that ignore that
        $html = RichTextToEmailHtmlConverter::process($richText, [
            RichTextToEmailHtmlConverter::OPTION_RECEIVER_USER => $this->notification->recipient,
        ]);

        return '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="table-layout: fixed;">'
            . '<tr><td style="word-wrap: break-word; max-width: ' . self::MAIL_WIDTH . 'px; '
            . $this->textStyle(14, MailStyleHelper::getTextColorMain(), '300') . '">'
            . $html
            . '</td></tr></table>';
    }

    private function renderQuoteHtml(NotificationBlock $block): string
    {
        $text = $this->encodeLines($block->getText());

        if ($block->getAuthor() !== null) {
            return MailCommentEntry::widget([
                'comment' => $text,
                'originator' => $block->getAuthor(),
                'receiver' => $this->notification->recipient,
                'date' => $block->getDate(),
            ]);
        }

        return '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: '
            . MailStyleHelper::getBackgroundColorSecondary() . '; border-radius: 4px;">'
            . '<tr><td style="padding: 10px; word-wrap: break-word; '
            . $this->textStyle(14, MailStyleHelper::getTextColorMain(), '300') . '">'
            . $text
            . '</td></tr></table>';
    }

    private function renderQuoteText(NotificationBlock $block): string
    {
        $lines = array_map(
            static fn(string $line): string => rtrim('> ' . $line),
            preg_split('/\R/', trim($block->getText())),
        );

        if (($author = $block->getAuthor()) !== null) {
            array_unshift($lines, $author->displayName . ':');
        }

        return implode("\n", $lines);
    }

    private function toPlainText(string $richText): string
    {
        return trim(RichTextToPlainTextConverter::process($richText));
    }

    private function encodeLines(string $text): string
    {
        return nl2br(Html::encode(trim($text)));
    }

    private function textStyle(int $fontSize, string $color, string $weight): string
    {
        return 'margin: 0; font-size: ' . $fontSize . 'px; line-height: 22px; font-family: '
            . MailStyleHelper::getFontFamily() . '; color: ' . $color . '; font-weight: ' . $weight
            . '; text-align: left;';
    }
}

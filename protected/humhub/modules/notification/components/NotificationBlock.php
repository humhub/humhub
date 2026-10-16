<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\user\models\User;

/**
 * A content block of a notification, shown under its sentence where the channel has room for
 * it, see {@see BaseNotification::getBlocks()}.
 *
 * The blocks are everything shown below the sentence, link buttons included. A block is semantic:
 * it says what it holds (a heading, plain text, RichText, a quote, a content preview, a link
 * button), the channel decides how it looks and how much of it fits - an HTML mail shows it in
 * full, a text mail as plain text.
 *
 * ```php
 * return [
 *     NotificationBlock::heading($news->title),
 *     NotificationBlock::richText($news->article),
 *     NotificationBlock::button(Yii::t('NewsModule.base', 'Confirm reading'), $confirmUrl),
 * ];
 * ```
 *
 * @api
 * @since 1.20
 */
final readonly class NotificationBlock
{
    /**
     * @internal
     */
    public const TYPE_HEADING = 'heading';

    /**
     * @internal
     */
    public const TYPE_TEXT = 'text';

    /**
     * @internal
     */
    public const TYPE_RICH_TEXT = 'richText';

    /**
     * @internal
     */
    public const TYPE_QUOTE = 'quote';

    /**
     * @internal
     */
    public const TYPE_CONTENT_PREVIEW = 'contentPreview';

    /**
     * @internal
     */
    public const TYPE_BUTTON = 'button';

    /**
     * @internal
     */
    public const TYPE_HTML = 'html';

    private function __construct(
        private string $type,
        private string $text = '',
        private ?string $html = null,
        private ?User $author = null,
        private string|int|null $date = null,
        private ?ContentOwner $record = null,
        private ?string $url = null,
    ) {
    }

    /**
     * A heading, e.g. the title of an article.
     *
     * @param string $text plain text
     * @api
     */
    public static function heading(string $text): self
    {
        return new self(self::TYPE_HEADING, $text);
    }

    /**
     * A paragraph of plain text; its line breaks are kept.
     *
     * @param string $text plain text
     * @api
     */
    public static function text(string $text): self
    {
        return new self(self::TYPE_TEXT, $text);
    }

    /**
     * HumHub RichText (Markdown), e.g. the text of an article, with its links, mentions and
     * images; a text mail shows it as plain text.
     *
     * @param string $richText the stored RichText
     * @api
     */
    public static function richText(string $richText): self
    {
        return new self(self::TYPE_RICH_TEXT, $richText);
    }

    /**
     * Plain text somebody wrote, e.g. the message of a membership request; an HTML mail shows
     * it as a card with the author and the date.
     *
     * @param string $text plain text, line breaks kept
     * @param User|null $author who wrote it
     * @param string|int|null $date when it was written: a database datetime or a Unix timestamp
     * @api
     */
    public static function quote(string $text, ?User $author = null, string|int|null $date = null): self
    {
        return new self(self::TYPE_QUOTE, $text, author: $author, date: $date);
    }

    /**
     * A preview of a content or a content addon (e.g. a comment), like on the stream; the
     * default block of a notification about a content, see {@see BaseNotification::getBlocks()}.
     *
     * @api
     */
    public static function contentPreview(ContentOwner $record): self
    {
        return new self(self::TYPE_CONTENT_PREVIEW, record: $record);
    }

    /**
     * A link button, e.g. "View online" or "Confirm reading". Consecutive buttons form one row of
     * buttons in an HTML mail, a text mail shows each as a `label: URL` line.
     *
     * @param string $label plain text
     * @param string $url an absolute URL
     * @api
     */
    public static function button(string $label, string $url): self
    {
        return new self(self::TYPE_BUTTON, $label, url: $url);
    }

    /**
     * Markup the other blocks cannot express, for an HTML mail only; every other output shows
     * `$text`. The HTML is output as is: encode what it contains and keep it mail-safe (tables,
     * inline styles).
     *
     * @param string $html the markup of the HTML mail
     * @param string $text the plain text alternative
     * @api
     */
    public static function html(string $html, string $text): self
    {
        return new self(self::TYPE_HTML, $text, $html);
    }

    /**
     * @return string one of the `TYPE_*` constants
     * @internal
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string the plain text, RichText, button label or text alternative; `''` for a content preview
     * @internal
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @internal
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @internal
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * @internal
     */
    public function getDate(): string|int|null
    {
        return $this->date;
    }

    /**
     * @internal
     */
    public function getRecord(): ?ContentOwner
    {
        return $this->record;
    }

    /**
     * @internal
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}

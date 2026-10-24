<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\helpers\Html;
use humhub\modules\content\helpers\ContentHelper;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\user\models\User;

/**
 * A parameter of a translated message (e.g. of a notification) whose value is rendered per output
 * format: HTML-encoded for HTML, as it is for plain text.
 *
 * ```php
 * return [
 *     'author' => MessageParam::user($author),                 // <strong>Anna</strong> / Anna
 *     'task' => MessageParam::content($task),                  // task "Prepare the release…"
 *     'spaceName' => MessageParam::emphasis($space->name),     // <strong>Sales</strong> / “Sales”
 *     'reason' => MessageParam::text($reason, maxLength: 100), // encoded / plain, at most 100 characters
 *     'version' => $version,                                    // a plain string: same as MessageParam::text()
 * ];
 * ```
 *
 * {@see renderAll()} renders a whole parameter array: plain strings are text, numbers (e.g. for an
 * ICU `plural`) stay numbers.
 *
 * A privacy mode that hides content ({@see renderRedacted()}) keeps user names and numbers, renders
 * a {@see content()} as its type alone ("task") and treats every other value as content.
 *
 * @api
 * @since 1.20
 */
final readonly class MessageParam
{
    /**
     * The default max length of the preview of a {@see content()}.
     */
    public const CONTENT_LENGTH = 60;

    private const ELLIPSIS = '…';

    /**
     * Stands in for a value that carries content while a redacted message is rendered: when it
     * shows up in the sentence, the sentence names content.
     */
    private const REDACTED = "\u{E000}";

    private function __construct(
        private string $text,
        private ?int $maxLength = null,
        private bool $strong = false,
        private bool $quoted = false,
        private ?string $html = null,
        private bool $user = false,
        private ?ContentOwner $content = null,
        private bool $withType = true,
        private bool $redactable = false,
    ) {
    }

    /**
     * Plain text: HTML-encoded for HTML, as it is for plain text.
     *
     * @param int|null $maxLength the text is cut to this many characters (with an ellipsis); `null` for no limit
     */
    public static function text(string $text, ?int $maxLength = null): self
    {
        return new self($text, $maxLength);
    }

    /**
     * Highlighted text, e.g. a name or a title: HTML-encoded in `<strong>` for HTML, in “quotes” for plain text.
     *
     * @param int|null $maxLength the text is cut to this many characters (with an ellipsis); `null` for no limit
     */
    public static function emphasis(string $text, ?int $maxLength = null): self
    {
        return new self($text, $maxLength, strong: true, quoted: true);
    }

    /**
     * The display name of a user: HTML-encoded in `<strong>` for HTML, as it is for plain text.
     */
    public static function user(User $user): self
    {
        return new self((string)$user->displayName, strong: true, user: true);
    }

    /**
     * A content (or content addon, e.g. a comment): its type and a preview of its text, e.g.
     * `post "Release notes…"` - the preview as short HTML for HTML, as plain text for plain text,
     * as long as the channel allows (short in the web, longer in a mail). Its type alone when it
     * has no text (e.g. a post of an image only) and in a privacy mode that hides content.
     *
     * @param bool $withType `false` for the preview alone, e.g. for "{displayName} created the task
     * {task}"; a privacy mode that hides content then treats it as content
     */
    public static function content(ContentOwner $record, bool $withType = true): self
    {
        return new self((string)$record->getContentName(), content: $record, withType: $withType, redactable: $withType);
    }

    /**
     * Whether the value is the display name of a user ({@see user()}) - a name, no content, so
     * a privacy mode that hides content may still show it.
     *
     * @internal for the redacted messages of notifications and activities
     */
    public function isUser(): bool
    {
        return $this->user;
    }

    /**
     * Markup the caller has encoded already, with its plain text twin.
     *
     * @param bool $redactable whether the value carries no content (e.g. a list of user names), so
     * a privacy mode that hides content may show it
     * @internal for values the core renders itself, e.g. the names of a group
     */
    public static function html(string $html, string $text, bool $redactable = false): self
    {
        return new self($text, html: $html, redactable: $redactable);
    }

    /**
     * @param int $contentLength the max length of the preview of a {@see content()}
     * @param bool $emphasizeContent whether a {@see content()} is wrapped in `<strong>` for HTML (in a mail)
     * @param bool $redacted whether a {@see content()} is rendered as its type alone, see {@see renderRedacted()}
     */
    public function render(
        MessageFormat $format,
        int $contentLength = self::CONTENT_LENGTH,
        bool $emphasizeContent = false,
        bool $redacted = false,
    ): string {
        if ($this->content !== null) {
            return $this->renderContent($format, $contentLength, $emphasizeContent, $redacted);
        }

        if ($this->html !== null) {
            return $format === MessageFormat::Html ? $this->html : $this->text;
        }

        $text = $this->truncate($this->text);

        if ($format === MessageFormat::Html) {
            $encoded = Html::encode($text);

            return $this->strong ? Html::tag('strong', $encoded) : $encoded;
        }

        return $this->quoted ? '“' . $text . '”' : $text;
    }

    /**
     * Renders the values of a parameter array: a {@see MessageParam} by {@see render()}, a string
     * like {@see text()}; numbers and other scalars are kept, e.g. for an ICU `plural`.
     *
     * @param array<string, MessageParam|string|int|float|bool|null> $params
     * @param int $contentLength the max length of the preview of a {@see content()}
     * @param bool $emphasizeContent whether a {@see content()} is wrapped in `<strong>` for HTML (in a mail)
     * @return array<string, string|int|float|bool|null>
     */
    public static function renderAll(
        array $params,
        MessageFormat $format,
        int $contentLength = self::CONTENT_LENGTH,
        bool $emphasizeContent = false,
    ): array {
        return array_map(static fn($value) => match (true) {
            $value instanceof self => $value->render($format, $contentLength, $emphasizeContent),
            is_string($value) => self::text($value)->render($format),
            default => $value,
        }, $params);
    }

    /**
     * The sentence `$message` builds from the parameters when it names no content: rendered with
     * user names, numbers and the type of each {@see content()} - `null` when the sentence uses
     * any other value (a text, a title, a Space name), which may be content.
     *
     * @param callable(array<string, string|int|float|bool|null>): string $message
     * @param array<string, MessageParam|string|int|float|bool|null> $params
     * @internal for the redacted messages of notifications and activities
     */
    public static function renderRedacted(callable $message, array $params, MessageFormat $format, bool $emphasizeContent = false): ?string
    {
        $rendered = array_map(static fn($value) => match (true) {
            $value instanceof self => $value->user || $value->redactable
                ? $value->render($format, emphasizeContent: $emphasizeContent, redacted: true)
                : self::REDACTED,
            is_string($value) => $value === '' ? '' : self::REDACTED,
            default => $value,
        }, $params);

        $sentence = $message($rendered);

        return str_contains($sentence, self::REDACTED) ? null : $sentence;
    }

    private function renderContent(MessageFormat $format, int $length, bool $emphasize, bool $redacted): string
    {
        // getContentInfo() returns HTML; its plain text twin is the decoded HTML
        $html = $redacted
            ? Html::encode($this->text)
            : ContentHelper::getContentInfo($this->content, $this->withType, $length);

        if ($format === MessageFormat::Html) {
            return $emphasize ? Html::tag('strong', $html) : $html;
        }

        return html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
    }

    private function truncate(string $text): string
    {
        if ($this->maxLength === null || mb_strlen($text) <= $this->maxLength) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(0, $this->maxLength - 1))) . self::ELLIPSIS;
    }
}

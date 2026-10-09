<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\message;

use humhub\helpers\Html;
use humhub\modules\user\models\User;

/**
 * A parameter of a translated message (e.g. of a notification) whose value is rendered per output
 * format: HTML-encoded for HTML, as it is for plain text.
 *
 * ```php
 * return [
 *     'spaceName' => MessageParam::emphasis($space->name),     // <strong>Sales</strong> / “Sales”
 *     'reason' => MessageParam::text($reason, maxLength: 100), // encoded / plain, at most 100 characters
 *     'version' => $version,                                    // a plain string: same as MessageParam::text()
 * ];
 * ```
 *
 * {@see renderAll()} renders a whole parameter array: plain strings are text, numbers (e.g. for an
 * ICU `plural`) stay numbers.
 *
 * @api
 * @since 1.20
 */
final readonly class MessageParam
{
    private const ELLIPSIS = '…';

    private function __construct(
        private string $text,
        private ?int $maxLength = null,
        private bool $strong = false,
        private bool $quoted = false,
        private ?string $html = null,
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
        return new self((string)$user->displayName, strong: true);
    }

    /**
     * Markup the caller has encoded already, with its plain text twin.
     *
     * @internal for values the core renders itself, e.g. the content preview of a notification
     */
    public static function html(string $html, string $text): self
    {
        return new self($text, html: $html);
    }

    public function render(MessageFormat $format): string
    {
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
     * @return array<string, string|int|float|bool|null>
     */
    public static function renderAll(array $params, MessageFormat $format): array
    {
        return array_map(static fn($value) => match (true) {
            $value instanceof self => $value->render($format),
            is_string($value) => self::text($value)->render($format),
            default => $value,
        }, $params);
    }

    private function truncate(string $text): string
    {
        if ($this->maxLength === null || mb_strlen($text) <= $this->maxLength) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(0, $this->maxLength - 1))) . self::ELLIPSIS;
    }
}

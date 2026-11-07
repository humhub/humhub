<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\extensions\emoji;

use humhub\components\ActiveRecord;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\base\Model;

/**
 * Converts the emoji shortcodes of rich texts saved before 1.20 (`:smile:`, `;smile;`) to the emoji itself.
 *
 * Rich texts store emojis as Unicode since 1.20. Older texts are converted when they are read (output, editor,
 * API, converters) and written back converted when they are saved again. Only names of the frozen map
 * `legacy-shortcodes.php` are converted, never inside code spans or code blocks.
 */
class RichTextEmojiExtension extends Model implements RichTextExtension
{
    /**
     * Fenced code blocks and code spans, which keep their text
     */
    private const CODE_REGEX = '/^ {0,3}(`{3,}|~{3,})[^\n]*\n.*?(?:^ {0,3}\1[`~]*[ \t]*$|\z)|(`+)(?:(?!\n[ \t]*\n).)*?(?<!`)\2(?!`)/ms';

    private static ?array $replacements = null;

    /**
     * @inheritdoc
     */
    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string
    {
        return static::convertEmojiToUtf8($output);
    }

    /**
     * @inheritdoc
     */
    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string
    {
        return $output;
    }

    /**
     * @inheritdoc
     */
    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        return static::convertEmojiToUtf8($text);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeConvert(string $text, string $format, array $options = []): string
    {
        return static::convertEmojiToUtf8($text);
    }

    /**
     * @inheritdoc
     */
    public function onAfterConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }

    /**
     * Replaces the legacy emoji shortcodes of the markdown text, except in code
     */
    public static function convertEmojiToUtf8(?string $text): string
    {
        $text = (string)$text;
        if (!str_contains($text, ':') && !str_contains($text, ';')) {
            return $text;
        }

        $result = '';
        $offset = 0;
        preg_match_all(self::CODE_REGEX, $text, $codes, PREG_OFFSET_CAPTURE);
        foreach ($codes[0] as [$code, $position]) {
            $result .= strtr(substr($text, $offset, $position - $offset), self::getReplacements()) . $code;
            $offset = $position + strlen($code);
        }

        return $result . strtr(substr($text, $offset), self::getReplacements());
    }

    /**
     * @return array<string, string> `:name:` and `;name;` => emoji
     */
    private static function getReplacements(): array
    {
        if (self::$replacements === null) {
            self::$replacements = [];
            foreach (require __DIR__ . '/legacy-shortcodes.php' as $name => $emoji) {
                self::$replacements[':' . $name . ':'] = $emoji;
                self::$replacements[';' . $name . ';'] = $emoji;
            }
        }

        return self::$replacements;
    }
}

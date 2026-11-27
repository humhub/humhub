<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use Yii;

/**
 * Checks and converts WebVTT subtitle texts (the format of a `<track>` element).
 *
 * The check covers what a player needs to show the cues: the `WEBVTT` header and the timing lines of the cues. It
 * doesn't parse cue settings or the markup of the cue text.
 *
 * @since 1.20
 */
class WebVtt
{
    /**
     * @var string a cue timestamp, `[hh:]mm:ss.ttt`
     */
    private const TIMESTAMP = '(?:\d{2,}:)?[0-5]\d:[0-5]\d\.\d{3}';

    /**
     * @var string a cue timing line, the settings after the end timestamp are not checked
     */
    private const TIMING = '/^(' . self::TIMESTAMP . ')[ \t]+-->[ \t]+(' . self::TIMESTAMP . ')(?:[ \t].*)?$/';

    /**
     * @var string a cue timing line of an SRT file, `hh:mm:ss,ttt --> hh:mm:ss,ttt`
     */
    private const SRT_TIMING = '/^\d{1,}:[0-5]\d:[0-5]\d[,.]\d{3}[ \t]+-->[ \t]+\d{1,}:[0-5]\d:[0-5]\d[,.]\d{3}\s*$/';

    /**
     * Removes the byte order mark and unifies the line breaks to `\n`
     */
    public static function normalize(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            $text = substr($text, 3);
        }

        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    /**
     * Whether the text is an SRT file rather than WebVTT: no `WEBVTT` header, but SRT timing lines
     */
    public static function isSrt(string $text): bool
    {
        $text = self::normalize($text);

        if (self::hasHeader($text)) {
            return false;
        }

        foreach (explode("\n", $text) as $line) {
            if (preg_match(self::SRT_TIMING, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts an SRT text to WebVTT: the cue numbers are dropped, the decimal comma of the timestamps becomes a
     * point and the header is added
     */
    public static function fromSrt(string $srt): string
    {
        $lines = explode("\n", self::normalize($srt));
        $out = ['WEBVTT', ''];

        foreach ($lines as $i => $line) {
            // A cue number is the line before a timing line
            if (preg_match('/^\d+$/', trim($line)) && isset($lines[$i + 1]) && preg_match(self::SRT_TIMING, $lines[$i + 1])) {
                continue;
            }

            if (preg_match(self::SRT_TIMING, $line)) {
                $line = preg_replace('/(\d),(\d{3})/', '$1.$2', trim($line));
                $line = preg_replace('/(?<!\d)(\d:\d{2}:)/', '0$1', $line);
            }

            $out[] = $line;
        }

        return rtrim(implode("\n", $out)) . "\n";
    }

    /**
     * Normalizes the text and converts it when it is an SRT text
     */
    public static function prepare(string $text): string
    {
        return self::isSrt($text) ? self::fromSrt($text) : self::normalize($text);
    }

    /**
     * Checks a WebVTT text
     *
     * @return string[] the errors, with the number of the line they refer to; empty when the text is valid
     */
    public static function validate(string $text): array
    {
        $text = self::normalize($text);
        $errors = [];

        if (trim($text) === '') {
            return [Yii::t('FileModule.base', 'The text is empty.')];
        }

        if (!self::hasHeader($text)) {
            $errors[] = Yii::t('FileModule.base', 'Line {line}: The text must start with "WEBVTT".', ['line' => 1]);
        }

        $cues = 0;
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            if (!str_contains($line, '-->')) {
                continue;
            }

            $number = $i + 1;

            if (!preg_match(self::TIMING, $line, $m)) {
                $errors[] = Yii::t('FileModule.base', 'Line {line}: Invalid cue timing, expected "[hh:]mm:ss.ttt --> [hh:]mm:ss.ttt".', ['line' => $number]);
                continue;
            }

            $cues++;

            if (self::seconds($m[1]) >= self::seconds($m[2])) {
                $errors[] = Yii::t('FileModule.base', 'Line {line}: The start of the cue must be before its end.', ['line' => $number]);
            }
        }

        if ($cues === 0 && $errors === []) {
            $errors[] = Yii::t('FileModule.base', 'The text contains no cue.');
        }

        return $errors;
    }

    /**
     * The header is `WEBVTT`, alone on its line or followed by a space or tab and a title
     */
    private static function hasHeader(string $text): bool
    {
        return (bool)preg_match('/^WEBVTT(?:[ \t].*)?(?:\n|$)/', $text);
    }

    private static function seconds(string $timestamp): float
    {
        $parts = array_map('floatval', explode(':', $timestamp));

        return array_reduce($parts, fn(float $sum, float $part) => $sum * 60 + $part, 0.0);
    }
}

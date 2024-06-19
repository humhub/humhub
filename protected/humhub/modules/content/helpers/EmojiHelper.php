<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\helpers;

use Yii;

/**
 * @since 1.16
 */
class EmojiHelper
{
    public const DATA_PATH = '@npm/unicode-emoji-json/data-by-emoji.json';

    public static function getData(): array
    {
        return Yii::$app->runtimeCache->getOrSet('emoji-helper-data', function () {
            $dataPath = Yii::getAlias(self::DATA_PATH);

            if (!is_file($dataPath)) {
                return [];
            }

            $data = (array) json_decode(file_get_contents($dataPath));

            $emojis = [];
            foreach ($data as $e => $emoji) {
                $emojis[$emoji->name] = $e;
            }

            return $emojis;
        });
    }

    public static function getUnicode(string $name): ?string
    {
        return self::getData()[$name] ?? null;
    }

    public static function findEmoji(string $content): ?string
    {
        return preg_match('/:([a-z\d\-\+][a-z\d\-\+\s_]*):/i', $content, $matches)
            ? $matches[1]
            : null;
    }
}

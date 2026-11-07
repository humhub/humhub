<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\helpers;

use humhub\helpers\EmojiHelper;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class EmojiHelperTest extends HumHubDbTestCase
{
    /**
     * @dataProvider languages
     */
    public function testDataLocale(string $language, string $locale)
    {
        $this->assertSame($locale, EmojiHelper::getDataLocale($language));
    }

    public static function languages(): array
    {
        return [
            ['de', 'de'],
            ['en-US', 'en'],
            ['en-GB', 'en-gb'],
            ['pt-BR', 'pt'],
            ['zh-CN', 'zh'],
            ['zh-TW', 'zh-hant'],
            ['nb-NO', 'nb'],
            ['fa-IR', 'en'],
            ['../../etc', 'en'],
        ];
    }

    public function testDataFileExistsForLocale()
    {
        $this->assertFileExists(Yii::getAlias(EmojiHelper::DATA_PATH) . '/' . EmojiHelper::getDataLocale('de') . '.json');
    }
}

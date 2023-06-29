<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\SettingActiveRecord;
use humhub\models\Setting;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidCallException;

class SettingActiveRecordTest extends HumHubDbTestCase
{
    public function testGetCacheKeyFormat()
    {
        $this->assertEquals('settings-%s', SettingActiveRecord::CACHE_KEY_FORMAT, "Cache key format changed!");
    }

    public function testGetCacheKeyFields()
    {
        $this->assertEquals(['module_id'], SettingActiveRecord::CACHE_KEY_FIELDS, "Cache key format changed!");
    }

    public function testGetCacheKey()
    {
        $cacheKey = Setting::getCacheKey('test');
        $this->assertEquals('settings-test', $cacheKey, "Cache key malformed!");

        $cacheKey = Setting::getCacheKey('test', 'more');
        $this->assertEquals('settings-test', $cacheKey, "Cache key malformed!");
    }

    public function testDeleteAll()
    {
        $this->expectException(InvalidCallException::class);
        $this->expectExceptionMessageRegExp(sprintf('@%s@', str_replace('\\', '\\\\', SettingActiveRecord::class)));

        SettingActiveRecord::deleteAll();
    }
}

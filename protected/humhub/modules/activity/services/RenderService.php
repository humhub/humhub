<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use Yii;
use yii\caching\Cache;
use yii\caching\DummyCache;

class RenderService
{
    private const OUTPUT_WEB = 1;
    private const OUTPUT_PLAINTEXT = 2;
    private const OUTPUT_MAIL = 3;
    private Cache $cache;
    const CACHE_DURATION = 120;

    public function __construct(private Activity $record, private bool $enableCaching = false)
    {
        $this->cache = $this->enableCaching ? Yii::$app->getCache() : new DummyCache();
    }

    public function getWeb(): ?string
    {
        return $this->cache->getOrSet($this->getCacheKey(self::OUTPUT_WEB), function () {
            return $this->getActivity()->renderWeb();
        }, self::CACHE_DURATION);
    }

    public function getPlaintext()
    {
        return $this->cache->getOrSet($this->getCacheKey(self::OUTPUT_PLAINTEXT), function () {
            return $this->getActivity()->renderPlaintext();
        }, self::CACHE_DURATION);
    }

    public function getMail()
    {
        return $this->cache->getOrSet($this->getCacheKey(self::OUTPUT_MAIL), function () {
            return $this->getActivity()->renderMail();
        }, self::CACHE_DURATION);
    }

    private function getActivity(): BaseActivity
    {
        // ToDo: Add cache
        return BaseActivity::factory($this->record);
    }

    private function getCacheKey(int $type): string
    {
        return sprintf('activity.%d-%d', $type, $this->record->id);
    }
}

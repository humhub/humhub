<?php

namespace humhub\tests\codeception\unit;

class UrlManagerMock extends \humhub\components\console\UrlManager
{
    public $configuredBaseUrl;

    protected function getConfiguredBaseUrl()
    {
        return $this->configuredBaseUrl ?: 'http://localhost';
    }
}

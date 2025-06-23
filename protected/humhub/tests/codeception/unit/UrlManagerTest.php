<?php

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\components\Application as WebApplication;
use humhub\components\console\Application as ConsoleApplication;
use humhub\helpers\ArrayHelper;
use humhub\libs\SafeBaseUrl;
use Yii;

/**
 * Unit tests for humhub\components\console\UrlManager
 */
class UrlManagerTest extends Unit
{
    public function testWebSafeBaseUrlWithNotRootBaseUrl()
    {
        $this->mock(
            WebApplication::class,
            'https://humhub.com/folder',
            '/folder/index-test.php'
        );

        $this->assertEquals(
            'https://humhub.com/folder/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    public function testWebSafeBaseUrlWithRootBaseUrl()
    {
        $this->mock(
            WebApplication::class,
            'https://humhub.com',
            '/index-test.php'
        );

        $this->assertEquals(
            'https://humhub.com/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    public function testWebSafeBaseUrlWithDefaultBaseUrl()
    {
        $this->mock(
            WebApplication::class
        );

        $this->assertEquals(
            'http://localhost/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    public function testConsoleSafeBaseUrlWithNotRootBaseUrl()
    {
        $this->mock(
            ConsoleApplication::class,
            'https://humhub.com/folder'
        );

        $this->assertEquals(
            'https://humhub.com/folder/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    public function testConsoleSafeBaseUrlWithRootBaseUrl()
    {
        $this->mock(
            ConsoleApplication::class,
            'https://humhub.com/'
        );

        $this->assertEquals(
            'https://humhub.com/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    public function testConsoleSafeBaseUrlWithDefaultBaseUrl()
    {
        $this->mock(
            ConsoleApplication::class
        );

        $this->assertEquals(
            'http://localhost/index-test.php?r=user%2Fprofile',
            SafeBaseUrl::to(['/user/profile'], true)
        );
    }

    private function mock($applicationType, $baseUrl = null, $scriptUrl = null)
    {
        $config = ArrayHelper::getValue(Yii::$app->getComponents(), 'urlManager', []);
        ArrayHelper::remove($config, 'class');

        Yii::$app = $this->createMock($applicationType);

        if ($baseUrl) {
            ArrayHelper::setValue($config, 'configuredBaseUrl', $baseUrl);
        }

        if ($scriptUrl) {
            ArrayHelper::setValue($config, 'scriptUrl', $scriptUrl);
        }

        SafeBaseUrl::$urlManager = new UrlManagerMock($config);
    }
}

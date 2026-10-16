<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\api;

use humhub\components\api\ApiRules;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Url;

/**
 * @see ApiRules
 */
class ApiRulesTest extends HumHubDbTestCase
{
    public function testRecognizesTheApiUrlSpace()
    {
        $this->assertTrue(ApiRules::isApiPath('api/v2/comment/1'));
        $this->assertTrue(ApiRules::isApiPath('api/v1/user'));
        $this->assertFalse(ApiRules::isApiPath('apiary/v2'));
        $this->assertFalse(ApiRules::isApiPath('comment/api/comment/view'));
    }

    /**
     * The URL an endpoint has for server-rendered markup: always the path form the URL rules
     * parse, never `index.php?r=…` - the API controllers refuse anything else.
     */
    public function testBuildsEndpointUrlsBelowTheVersionPrefix()
    {
        $base = rtrim(Url::base(), '/');

        $this->assertSame($base . '/api/v2/', ApiRules::url());
        $this->assertSame($base . '/api/v2/space/3/membership', ApiRules::url('space/3/membership'));
        $this->assertSame($base . '/api/v2/space/3/membership', ApiRules::url('/space/3/membership'));
        $this->assertStringNotContainsString('?r=', ApiRules::url('space/3/membership'));
    }
}

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
    public function testPrefixesPatternsWithTheVersionPrefix()
    {
        $rules = ApiRules::v2([
            ['pattern' => 'comment/<id:\d+>', 'route' => 'comment/api/comment/view', 'verb' => ['GET', 'HEAD']],
            ['pattern' => 'like', 'route' => 'like/api/like/create', 'verb' => 'POST'],
        ]);

        $this->assertSame('api/v2/comment/<id:\d+>', $rules[0]['pattern']);
        $this->assertSame('api/v2/like', $rules[1]['pattern']);

        // Everything else passes through untouched
        $this->assertSame('comment/api/comment/view', $rules[0]['route']);
        $this->assertSame(['GET', 'HEAD'], $rules[0]['verb']);
        $this->assertSame('POST', $rules[1]['verb']);
    }

    public function testNormalizesLeadingSlashesInsteadOfDoublingThem()
    {
        $rules = ApiRules::v2([['pattern' => '/comment/window', 'route' => 'comment/api/comment/window']]);

        $this->assertSame('api/v2/comment/window', $rules[0]['pattern']);
    }

    public function testLeavesRulesWithoutAPatternAlone()
    {
        // A rule given as a class configuration (UrlRule implementation) has no pattern
        $rules = ApiRules::v2([['class' => 'app\components\SomeUrlRule']]);

        $this->assertSame([['class' => 'app\components\SomeUrlRule']], $rules);
    }

    public function testEmptyRuleSetStaysEmpty()
    {
        $this->assertSame([], ApiRules::v2([]));
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

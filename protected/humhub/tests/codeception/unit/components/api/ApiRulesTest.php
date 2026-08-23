<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\api;

use humhub\components\api\ApiRules;
use tests\codeception\_support\HumHubDbTestCase;

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
}

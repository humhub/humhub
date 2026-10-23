<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\rateLimit;

use Codeception\Test\Unit;
use humhub\components\rateLimit\ApiRateLimitFilter;
use humhub\components\rateLimit\RateLimiter;
use humhub\modules\oauth\components\ScopeFilter;

/**
 * @since 1.20
 */
class ApiRateLimitFilterTest extends Unit
{
    private const WINDOW = ['burst' => ['limit' => 10, 'span' => 60]];

    private function limiter(array $policies): RateLimiter
    {
        return new RateLimiter(['policies' => $policies]);
    }

    public function testExactScopeWins()
    {
        $limiter = $this->limiter([
            'token.content:write' => self::WINDOW,
            'token.*:write' => self::WINDOW,
            'token.full' => self::WINDOW,
        ]);

        $this->assertSame('token.content:write', ApiRateLimitFilter::resolvePolicy($limiter, 'token', 'content:write'));
    }

    public function testSuffixPolicy()
    {
        $limiter = $this->limiter(['token.*:read' => self::WINDOW, 'token.full' => self::WINDOW]);

        $this->assertSame('token.*:read', ApiRateLimitFilter::resolvePolicy($limiter, 'token', 'calendar:read'));
        $this->assertSame('token.full', ApiRateLimitFilter::resolvePolicy($limiter, 'token', 'calendar:write'));
    }

    public function testSuffixIsTheTextAfterTheLastColon()
    {
        $limiter = $this->limiter(['session.*:read' => self::WINDOW]);

        $this->assertSame('session.*:read', ApiRateLimitFilter::resolvePolicy($limiter, 'session', 'mod:sub:read'));
    }

    public function testFullFallback()
    {
        $limiter = $this->limiter(['session.full' => self::WINDOW, 'token.*:read' => self::WINDOW]);

        $this->assertSame('session.full', ApiRateLimitFilter::resolvePolicy($limiter, 'session', 'content:read'));
        $this->assertSame('session.full', ApiRateLimitFilter::resolvePolicy($limiter, 'session', 'full'));
        $this->assertNull(ApiRateLimitFilter::resolvePolicy($limiter, 'token', 'content:write'));
    }

    public function testDisabledPolicyStopsTheLookup()
    {
        $limiter = $this->limiter(['session.*:read' => false, 'session.full' => self::WINDOW]);

        $this->assertSame('session.*:read', ApiRateLimitFilter::resolvePolicy($limiter, 'session', 'content:read'));
        $this->assertNull($limiter->getPolicy('session.*:read'));
    }

    public function testScopeOfAnAction()
    {
        $scopes = ['view' => 'content:read', 'list' => ['content:read', 'space:read'], '*' => 'content:write'];

        $this->assertSame('content:read', ApiRateLimitFilter::scopeOf($scopes, 'view'));
        $this->assertSame('content:read', ApiRateLimitFilter::scopeOf($scopes, 'list'), 'A list counts as its first entry');
        $this->assertSame('content:write', ApiRateLimitFilter::scopeOf($scopes, 'delete'));
        $this->assertSame('full', ApiRateLimitFilter::scopeOf([], 'view'));
        $this->assertSame('full', ApiRateLimitFilter::scopeOf(['view' => []], 'view'));
    }

    public function testResolveScope()
    {
        $scopes = ['view' => 'a:read', 'list' => ['a:read', 'b:read'], '*' => 'a:write'];

        $this->assertSame('a:read', ScopeFilter::resolveScope($scopes, 'view'));
        $this->assertSame(['a:read', 'b:read'], ScopeFilter::resolveScope($scopes, 'list'));
        $this->assertSame('a:write', ScopeFilter::resolveScope($scopes, 'other'));
        $this->assertNull(ScopeFilter::resolveScope([], 'view'));
    }
}

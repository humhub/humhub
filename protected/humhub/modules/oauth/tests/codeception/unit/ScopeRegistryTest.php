<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\events\ScopesEvent;
use humhub\modules\oauth\models\Scope;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

class ScopeRegistryTest extends HumHubDbTestCase
{
    private ?\Closure $handler = null;

    protected function tearDown(): void
    {
        if ($this->handler !== null) {
            Event::off(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, $this->handler);
            $this->handler = null;
        }
        ScopeRegistry::reset();
        parent::tearDown();
    }

    public function testCoreScopes()
    {
        $ids = array_keys(ScopeRegistry::getScopes());

        $this->assertContains('full', $ids);
        $this->assertContains('content:read', $ids);
        $this->assertContains('content:write', $ids);
        $this->assertSame(
            ['account:read', 'notifications:read', 'users:read', 'spaces:read', 'content:read'],
            ScopeRegistry::getDefaultRestrictedScopeIds(),
        );
    }

    public function testModulesAppendScopes()
    {
        $this->handler = function (ScopesEvent $event) {
            $event->scopes[] = new Scope('calendar:read', 'Read calendars');
            // reserved and duplicate ids are ignored
            $event->scopes[] = new Scope('full', 'Hijack');
            $event->scopes[] = new Scope('content:read', 'Duplicate');
        };
        Event::on(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, $this->handler);
        ScopeRegistry::reset();

        $this->assertTrue(ScopeRegistry::has('calendar:read'));
        $this->assertSame('Read calendars', ScopeRegistry::get('calendar:read')->label);
        $this->assertNotSame('Hijack', ScopeRegistry::get('full')->label);
        $this->assertNotSame('Duplicate', ScopeRegistry::get('content:read')->label);
    }

    public function testModuleScopesNeedAModulePrefix()
    {
        $this->handler = function (ScopesEvent $event) {
            $event->scopes[] = new Scope('calendar:entries:read', 'Read entries');
            $event->scopes[] = new Scope('calendar', 'No prefix');
            $event->scopes[] = new Scope('Calendar:read', 'Upper case');
            $event->scopes[] = new Scope(':read', 'Empty module');
            $event->scopes[] = new Scope('calendar:read write', 'Space');
        };
        Event::on(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, $this->handler);
        ScopeRegistry::reset();

        $this->assertTrue(ScopeRegistry::has('calendar:entries:read'));
        $this->assertFalse(ScopeRegistry::has('calendar'));
        $this->assertFalse(ScopeRegistry::has('Calendar:read'));
        $this->assertFalse(ScopeRegistry::has(':read'));
        $this->assertFalse(ScopeRegistry::has('calendar:read write'));
        $this->assertTrue(ScopeRegistry::has('full'), 'core scopes are not subject to the module prefix');
    }
}

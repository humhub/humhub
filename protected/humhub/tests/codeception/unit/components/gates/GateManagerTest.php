<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\gates;

use humhub\components\gates\GateInitEvent;
use humhub\components\gates\GateManager;
use humhub\components\gates\RequestClass;
use humhub\components\gates\UserGate;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

class GateManagerTest extends HumHubDbTestCase
{
    public $fixtureConfig = [
        'default',
    ];

    protected function _after()
    {
        Event::off(GateManager::class, GateManager::EVENT_INIT_GATES);
        parent::_after();
    }

    private function createManager(FakeGate ...$gates): GateManager
    {
        $manager = new GateManager();
        foreach ($gates as $gate) {
            $manager->register($gate);
        }
        return $manager;
    }

    public function testGatesAreCollectedViaInitEventAndSorted()
    {
        Event::on(GateManager::class, GateManager::EVENT_INIT_GATES, function (GateInitEvent $event) {
            $event->manager->register(new FakeGate(['id' => 'late', 'sortOrder' => 300]));
            $event->manager->register(new FakeGate(['id' => 'early', 'sortOrder' => 100]));
        });

        $manager = new GateManager();
        // Other modules (e.g. the user module) may register additional gates through the
        // same event, so only assert presence and relative order of the test gates.
        $ids = array_map(fn($gate) => $gate->getId(), $manager->getGates());

        $this->assertContains('early', $ids);
        $this->assertContains('late', $ids);
        $this->assertLessThan(array_search('late', $ids), array_search('early', $ids));
    }

    public function testDeregisterRemovesGate()
    {
        $manager = $this->createManager(
            new FakeGate(['id' => 'a', 'sortOrder' => 100]),
            new FakeGate(['id' => 'b', 'sortOrder' => 200]),
        );

        $manager->deregister('a');

        $ids = array_map(fn($gate) => $gate->getId(), $manager->getGates());
        $this->assertContains('b', $ids);
        $this->assertNotContains('a', $ids);
    }

    public function testFindOpenGateReturnsFirstOpenApplicableGate()
    {
        $manager = $this->createManager(
            new FakeGate(['id' => 'a', 'sortOrder' => 100, 'open' => false]),
            new FakeGate(['id' => 'b', 'sortOrder' => 200, 'open' => true]),
            new FakeGate(['id' => 'c', 'sortOrder' => 300, 'open' => true]),
        );

        $gate = $manager->findOpenGate(RequestClass::FullPage, 'dashboard');

        $this->assertNotNull($gate);
        $this->assertEquals('b', $gate->getId());
    }

    public function testGateDoesNotInterceptItsOwnRoute()
    {
        $manager = $this->createManager(
            new FakeGate(['id' => 'b', 'sortOrder' => 200, 'open' => true, 'route' => ['/b/check']]),
            new FakeGate(['id' => 'c', 'sortOrder' => 300, 'open' => true]),
        );

        // The open gate owns the route: no interception, and the later gate 'c' must not
        // intercept it either (funnel ordering).
        $this->assertNull($manager->findOpenGate(RequestClass::FullPage, 'b/check'));

        // Routes are matched by path segment prefix (controller route incl. action id)
        $this->assertNull($manager->findOpenGate(RequestClass::FullPage, 'b/check/index'));

        // But an unrelated route sharing the prefix as substring is not owned
        $gate = $manager->findOpenGate(RequestClass::FullPage, 'b/checkother');
        $this->assertEquals('b', $gate?->getId());
    }

    public function testEarlierGateInterceptsLaterGatesRoute()
    {
        $manager = $this->createManager(
            new FakeGate(['id' => 'a', 'sortOrder' => 100, 'open' => true, 'route' => ['/a/flow']]),
            new FakeGate(['id' => 'b', 'sortOrder' => 200, 'open' => true, 'route' => ['/b/check']]),
        );

        // The password-style gate 'a' may pull the user away from gate 'b's own page
        $gate = $manager->findOpenGate(RequestClass::FullPage, 'b/check');

        $this->assertEquals('a', $gate?->getId());
    }

    public function testAllowedRoutesShieldFromLaterGates()
    {
        $manager = $this->createManager(
            new FakeGate([
                'id' => 'b',
                'sortOrder' => 200,
                'open' => true,
                'route' => ['/b/check'],
                'allowedRoutes' => ['user/auth/logout'],
            ]),
            new FakeGate(['id' => 'c', 'sortOrder' => 300, 'open' => true]),
        );

        $this->assertNull($manager->findOpenGate(RequestClass::FullPage, 'user/auth/logout'));
    }

    public function testAppliesToFiltersRequestClass()
    {
        $manager = $this->createManager(
            new FakeGate([
                'id' => 'b',
                'sortOrder' => 200,
                'open' => true,
                'applies' => [RequestClass::FullPage],
            ]),
            new FakeGate(['id' => 'c', 'sortOrder' => 300, 'open' => true]),
        );

        // 'b' does not apply to Ajax, so the next open gate 'c' wins
        $gate = $manager->findOpenGate(RequestClass::Ajax, 'dashboard');
        $this->assertEquals('c', $gate?->getId());

        // For full page requests 'b' wins
        $gate = $manager->findOpenGate(RequestClass::FullPage, 'dashboard');
        $this->assertEquals('b', $gate?->getId());
    }

    public function testAllClosedSnapshotSkipsCacheableGates()
    {
        $gate = new FakeGate(['id' => 'b', 'open' => false, 'cacheable' => true]);
        $manager = $this->createManager($gate);

        $this->assertNull($manager->findOpenGate(RequestClass::FullPage, 'dashboard'));
        $this->assertNull($manager->findOpenGate(RequestClass::FullPage, 'dashboard'));

        $this->assertEquals(1, $gate->isOpenCalls, 'closed cacheable gate must be evaluated only once');
    }

    public function testNonCacheableGateIsAlwaysEvaluated()
    {
        $nonCacheable = new FakeGate(['id' => 'b', 'sortOrder' => 100, 'open' => false, 'cacheable' => false]);
        $cacheable = new FakeGate(['id' => 'c', 'sortOrder' => 200, 'open' => false, 'cacheable' => true]);
        $manager = $this->createManager($nonCacheable, $cacheable);

        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');
        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');

        $this->assertEquals(2, $nonCacheable->isOpenCalls, 'non-cacheable gate must be evaluated on every request');
        $this->assertEquals(1, $cacheable->isOpenCalls, 'cacheable gate must be covered by the snapshot');
    }

    public function testOpenGatePreventsSnapshot()
    {
        $gate = new FakeGate(['id' => 'b', 'open' => true, 'cacheable' => true]);
        $manager = $this->createManager($gate);

        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');
        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');

        $this->assertEquals(2, $gate->isOpenCalls, 'open gate must prevent the all-closed snapshot');
    }

    public function testInvalidateForcesReevaluation()
    {
        $gate = new FakeGate(['id' => 'b', 'open' => false, 'cacheable' => true]);
        $manager = $this->createManager($gate);

        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');
        $manager->invalidate();
        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');

        $this->assertEquals(2, $gate->isOpenCalls, 'invalidate() must discard the all-closed snapshot');
    }

    public function testSnapshotIsBoundToUser()
    {
        $gate = new FakeGate(['id' => 'b', 'open' => false, 'cacheable' => true]);
        $manager = $this->createManager($gate);

        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');
        static::becomeUser('User1');
        $manager->findOpenGate(RequestClass::FullPage, 'dashboard');

        $this->assertEquals(2, $gate->isOpenCalls, 'identity switch must discard the all-closed snapshot');
    }

    public function testBaseClassDefaults()
    {
        $gate = new class extends UserGate {
            public function getId(): string
            {
                return 'defaults';
            }

            public function getSortOrder(): int
            {
                return self::SORT_ONBOARDING;
            }

            public function isOpen(): bool
            {
                return false;
            }

            public function getRoute(): array
            {
                return ['/defaults/flow'];
            }
        };

        $this->assertEquals([], $gate->getAllowedRoutes());
        $this->assertTrue($gate->appliesTo(RequestClass::FullPage));
        $this->assertTrue($gate->appliesTo(RequestClass::Ajax));
        $this->assertFalse($gate->appliesTo(RequestClass::Api));
        $this->assertTrue($gate->isCacheable());
    }
}

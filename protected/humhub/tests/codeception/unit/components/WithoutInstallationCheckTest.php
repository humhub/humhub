<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\InstallationState;
use humhub\components\WithoutInstallationCheck;
use humhub\controllers\HealthController;
use ReflectionProperty;
use Yii;
use yii\base\InlineAction;
use yii\web\Controller;

/**
 * @since 1.20
 */
class WithoutInstallationCheckTest extends Unit
{
    private ?InstallationState $originalState = null;

    protected function setUp(): void
    {
        parent::setUp();
        $instances = (new ReflectionProperty(InstallationState::class, '_instances'))->getValue();
        $this->originalState = $instances[InstallationState::class] ?? null;
    }

    protected function tearDown(): void
    {
        // Restore exactly the instance that existed before, never rebuild it from the (possibly empty) database
        $this->setInstallationState($this->originalState);
        parent::tearDown();
    }

    public function testDetectsTheAttribute(): void
    {
        $this->assertTrue(WithoutInstallationCheck::isPresentOn(new HealthController('health', Yii::$app)));
        $this->assertFalse(WithoutInstallationCheck::isPresentOn(new Controller('other', Yii::$app)));
    }

    public function testAnAttributedControllerIsNotSentToTheInstaller(): void
    {
        $this->setInstallationState($this->notInstalled());

        $this->assertTrue($this->beforeAction(new HealthController('health', Yii::$app), 'ready'));
        $this->assertFalse($this->beforeAction(new Controller('other', Yii::$app), 'index'));
    }

    private function beforeAction(Controller $controller, string $actionId): bool
    {
        Yii::$app->controller = $controller;
        $action = new InlineAction($actionId, $controller, 'action' . ucfirst($actionId));

        return Yii::$app->beforeAction($action);
    }

    private function notInstalled(): InstallationState
    {
        $mock = $this->createMock(InstallationState::class);
        $mock->method('hasState')->willReturn(false);
        $mock->method('isDatabaseUnreachable')->willReturn(false);

        return $mock;
    }

    /**
     * InstallationState uses StaticInstanceTrait; put a mock into its private instance map.
     */
    private function setInstallationState(?InstallationState $state): void
    {
        $property = new ReflectionProperty(InstallationState::class, '_instances');
        $instances = $property->getValue();
        if ($state === null) {
            unset($instances[InstallationState::class]);
        } else {
            $instances[InstallationState::class] = $state;
        }
        $property->setValue(null, $instances);
    }
}

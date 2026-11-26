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
    protected function tearDown(): void
    {
        $this->setInstallationState(null);
        InstallationState::instance(true);
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

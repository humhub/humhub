<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\commands;

use humhub\commands\HealthController;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\console\ExitCode;

/**
 * @since 1.20
 */
class HealthControllerTest extends HumHubDbTestCase
{
    private function createController(array $options = []): HealthController
    {
        $controller = new class ('health', Yii::$app) extends HealthController {
            public string $output = '';
            public string $errorOutput = '';

            public function stdout($string)
            {
                $this->output .= $string;
                return strlen($string);
            }

            public function stderr($string)
            {
                $this->errorOutput .= $string;
                return strlen($string);
            }
        };

        foreach ($options as $name => $value) {
            $controller->$name = $value;
        }

        return $controller;
    }

    public function testReadyAsText(): void
    {
        $controller = $this->createController(['check' => 'ready']);

        $exitCode = $controller->actionStatus();

        $this->assertContains($exitCode, [0, 1]);
        $this->assertMatchesRegularExpression('/^(OK|WARNING)  installed  HumHub /', $controller->output);
        $this->assertStringContainsString('  database', $controller->output);
    }

    public function testJson(): void
    {
        $controller = $this->createController(['check' => 'live', 'format' => 'json']);

        $this->assertSame(ExitCode::OK, $controller->actionStatus());
        $data = json_decode($controller->output, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(1, $data['format']);
        $this->assertSame('live', $data['level']);
        $this->assertArrayHasKey('filesystem:runtime', $data['checks']);
    }

    public function testFailOnFailureMapsWarningsToZero(): void
    {
        Yii::$app->settings->set('cronLastRun', 0); // cron never ran: warning

        $default = $this->createController(['check' => 'all']);
        $failureOnly = $this->createController(['check' => 'all', 'failOn' => 'failure']);

        $this->assertGreaterThanOrEqual(1, $default->actionStatus());
        $this->assertContains($failureOnly->actionStatus(), [0, 1]);
    }

    public function testInvalidOptions(): void
    {
        $controller = $this->createController(['check' => 'nope']);

        $this->assertSame(ExitCode::USAGE, $controller->actionStatus());
        $this->assertStringContainsString('--check', $controller->errorOutput);
    }

    public function testOptionsAreDeclared(): void
    {
        $this->assertEqualsCanonicalizing(
            ['check', 'format', 'failOn'],
            array_values(array_intersect(['check', 'format', 'failOn'], $this->createController()->options('status'))),
        );
    }
}

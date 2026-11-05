<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\commands;

use humhub\commands\VersionController;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\console\ExitCode;

/**
 * @since 1.20
 */
class VersionControllerTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    protected function tearDown(): void
    {
        unset(Yii::$app->params['docker']);

        parent::tearDown();
    }

    public function testIndexPrintsVersionOnly(): void
    {
        $controller = $this->createController();

        static::assertSame(ExitCode::OK, $controller->actionIndex());
        static::assertSame(Yii::$app->version . PHP_EOL, $controller->output);
    }

    public function testInfo(): void
    {
        $info = $this->createController()->getInfo();

        static::assertSame(Yii::$app->version, $info['HumHub version']);
        static::assertSame('Yes', $info['Installed']);
        static::assertSame(PHP_VERSION, $info['PHP version']);
        static::assertStringStartsWith(Yii::$app->db->getDriverName() . ' ', $info['Database']);
        static::assertArrayNotHasKey('Docker image tag', $info);
    }

    public function testInfoIncludesDockerImage(): void
    {
        Yii::$app->params['docker'] = ['imageTag' => '1.20.0', 'coreRevision' => 'abc123', 'imageRevision' => ''];

        $info = $this->createController()->getInfo();

        static::assertSame('1.20.0', $info['Docker image tag']);
        static::assertSame('abc123', $info['HumHub core commit']);
        static::assertArrayNotHasKey('Docker repository commit', $info);
    }

    public function testInfoPrintsAllDetails(): void
    {
        $controller = $this->createController();

        static::assertSame(ExitCode::OK, $controller->actionInfo());
        foreach ($controller->getInfo() as $label => $value) {
            static::assertMatchesRegularExpression(
                '/^' . preg_quote($label, '/') . ':\s+' . preg_quote($value, '/') . '$/m',
                $controller->output,
            );
        }
    }

    private function createController(): VersionController
    {
        return new class ('version', Yii::$app) extends VersionController {
            public string $output = '';

            public function stdout($string)
            {
                $this->output .= $string;
                return strlen($string);
            }
        };
    }
}

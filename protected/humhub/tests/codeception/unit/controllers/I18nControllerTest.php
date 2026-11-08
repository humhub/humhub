<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\controllers;

use Codeception\Test\Unit;
use humhub\components\i18n\PhpMessageSource;
use humhub\controllers\I18nController;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Response;

/**
 * @since 1.20
 */
class I18nControllerTest extends Unit
{
    private const CATEGORY = 'I18nControllerTest.test';

    private string $language;

    protected function setUp(): void
    {
        parent::setUp();

        $basePath = Yii::getAlias('@runtime/i18n-controller-test');
        FileHelper::createDirectory($basePath . '/de');
        file_put_contents($basePath . '/de/' . self::CATEGORY . '.php', "<?php\nreturn [\n    'Translated' => 'Übersetzt',\n    'Untranslated' => '',\n];\n");

        Yii::$app->i18n->translations[self::CATEGORY] = ['class' => PhpMessageSource::class, 'basePath' => $basePath];
        $this->language = Yii::$app->language;
        Yii::$app->language = 'de';
    }

    protected function tearDown(): void
    {
        Yii::$app->language = $this->language;
        unset(Yii::$app->i18n->translations[self::CATEGORY]);
        FileHelper::removeDirectory(Yii::getAlias('@runtime/i18n-controller-test'));
        Yii::$app->response->format = Response::FORMAT_HTML;
        parent::tearDown();
    }

    /**
     * The client shows the source message of a message without translation, as `Yii::t()` does,
     * so an empty translation must not reach it.
     */
    public function testUntranslatedMessagesAreLeftOut()
    {
        $controller = new I18nController('i18n', Yii::$app);

        $response = $controller->actionTranslations(self::CATEGORY);

        $this->assertSame('de', $response->data['locale']);
        $this->assertSame(['Translated' => 'Übersetzt'], $response->data['messages'][self::CATEGORY]);
    }
}

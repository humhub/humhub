<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\controllers;

use humhub\components\i18n\PhpMessageSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * The translations endpoint hands the requested message categories to the message sources,
 * which build the translation file path from them.
 */
class I18nControllerTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public static function invalidCategoryProvider(): array
    {
        return [
            'parent directory' => ['../base'],
            'slash' => ['base/base'],
            'backslash' => ['base\\base'],
            'module prefix with path' => ['UserModule.../base'],
            'dot-dot' => ['UserModule...'],
            'empty' => [''],
        ];
    }

    /**
     * @dataProvider invalidCategoryProvider
     */
    public function testRejectsCategoryThatIsNotAPlainName(string $category)
    {
        $this->becomeUser('User1');

        $this->expectException(BadRequestHttpException::class);
        $this->requestTranslations($category);
    }

    public function testDeliversTranslationsOfValidCategories()
    {
        $this->becomeUser('User1');
        Yii::$app->language = 'de';

        $data = $this->requestTranslations('base,UserModule.base')->data;

        $this->assertSame('de', $data['locale']);
        $this->assertSame('Speichern', $data['messages']['base']['Save']);
        $this->assertNotEmpty($data['messages']['UserModule.base']);
    }

    public function testIsReachableWithoutLogin()
    {
        // The login page loads its JavaScript translations through this endpoint
        $data = $this->requestTranslations('base')->data;

        $this->assertArrayHasKey('base', $data['messages']);
    }

    public function testOmitsUntranslatedMessages()
    {
        $this->becomeUser('User1');
        Yii::$app->language = 'de';
        Yii::$app->i18n->translations['testcat'] = [
            'class' => PhpMessageSource::class,
            'basePath' => dirname(__DIR__, 2) . '/_data/messages',
        ];

        $messages = $this->requestTranslations('testcat')->data['messages']['testcat'];

        $this->assertSame(['Hello' => 'Hallo'], $messages);
    }

    private function requestTranslations(string $category): Response
    {
        return Yii::$app->runAction('i18n/translations', ['category' => $category]);
    }
}

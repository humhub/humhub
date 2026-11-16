<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\components\UrlValidator;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\DynamicModel;

class UrlValidatorTest extends HumHubDbTestCase
{
    /**
     * @dataProvider generatedUrlProvider
     */
    public function testAutogenerateTransliteratesName(string $name, string $expectedUrl): void
    {
        Yii::$app->language = 'en-US';

        $this->assertSame($expectedUrl, UrlValidator::autogenerateUniqueSpaceUrl($name));
    }

    public static function generatedUrlProvider(): array
    {
        return [
            ['Über uns', 'uber-uns'],
            ['Привет мир', 'privet-mir'],
            ['会议室', 'hui-yi-shi'],
            ['Café & Bar', 'cafe-bar'],
            ["Don't panic", 'dont-panic'],
            ['  Projekt: Neue Website (2026)  ', 'projekt-neue-website-2026'],
            ['🎉', ''],
        ];
    }

    /**
     * @dataProvider germanUrlProvider
     */
    public function testAutogenerateUsesGermanRulesForGermanLanguage(string $name, string $expectedUrl): void
    {
        Yii::$app->language = 'de';

        $this->assertSame($expectedUrl, UrlValidator::autogenerateUniqueSpaceUrl($name));
    }

    public static function germanUrlProvider(): array
    {
        return [
            ['Über uns', 'ueber-uns'],
            ['Größe & Maß', 'groesse-mass'],
            ['GROẞE ÜBUNG', 'grosse-uebung'],
            ['Café Zürich', 'cafe-zuerich'],
            ['Привет мир', 'privet-mir'],
        ];
    }

    /**
     * @dataProvider elisionUrlProvider
     */
    public function testAutogenerateSeparatesElidedWords(string $language, string $name, string $expectedUrl): void
    {
        Yii::$app->language = $language;

        $this->assertSame($expectedUrl, UrlValidator::autogenerateUniqueSpaceUrl($name));
    }

    public static function elisionUrlProvider(): array
    {
        return [
            ['fr', "L'équipe de France", 'l-equipe-de-france'],
            ['fr', 'L’équipe d’été', 'l-equipe-d-ete'],
            ['it', "Dell'Arte", 'dell-arte'],
            ['it', 'L’Aquila', 'l-aquila'],
            ['ca', "Col·legi d'Òmnium", 'collegi-d-omnium'],
            ['en-US', "Don't panic", 'dont-panic'],
        ];
    }

    public function testAutogenerateTruncatesLongName(): void
    {
        $this->assertSame(str_repeat('x', 41), UrlValidator::autogenerateUniqueSpaceUrl(str_repeat('x', 60)));
        $this->assertSame(str_repeat('a', 40), UrlValidator::autogenerateUniqueSpaceUrl(str_repeat('a', 40) . ' bbbbbbbbbb'));
    }

    public function testAutogenerateAppendsNumberToTakenUrl(): void
    {
        Space::updateAll(['url' => 'team'], ['id' => 1]);

        $this->assertSame('team2', UrlValidator::autogenerateUniqueSpaceUrl('Team'));
    }

    /**
     * @dataProvider urlProvider
     */
    public function testValidateAttribute(string $url, bool $isValid): void
    {
        $model = new DynamicModel(['url' => $url]);
        $validator = new UrlValidator(['space' => Space::findOne(['id' => 1])]);
        $validator->validateAttribute($model, 'url');

        $this->assertSame(!$isValid, $model->hasErrors('url'), implode(' ', $model->getErrors('url')));
    }

    public static function urlProvider(): array
    {
        return [
            ['my-space', true],
            ['space2026', true],
            ['Team-Space', true],
            ['123', true],
            ['my space', false],
            ['my_space', false],
            ['über-uns', false],
            ['my--space', false],
            ['-space', false],
            ['space-', false],
            ['foo.bar', false],
            ['sales/marketing', false],
            ['space-2', false],
        ];
    }
}

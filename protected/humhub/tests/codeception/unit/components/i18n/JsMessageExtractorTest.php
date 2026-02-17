<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\tests\codeception\unit\components\i18n;

use humhub\components\i18n\JsMessageExtractor;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class JsMessageExtractorTest extends HumHubDbTestCase
{
    /**
     * @var JsMessageExtractor
     */
    protected $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new JsMessageExtractor();
    }

    public function testBasicExtraction()
    {
        $jsFile = Yii::getAlias('@runtime/test_extract.js');
        $content = "i18n.t('Category', 'Message');";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, 'i18n.t');
        $this->assertArrayHasKey('Category', $messages);
        $this->assertContains('Message', $messages['Category']);

        unlink($jsFile);
    }

    public function testQuotesAndSpaces()
    {
        $jsFile = Yii::getAlias('@runtime/test_extract_quotes.js');
        $content = "
            i18n.t(\"Double\", \"Double Message\");
            i18n.t( 'Single' , 'Single Message' );
            i18n.t('Mixed', \"Mixed Message\");
        ";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, 'i18n.t');

        $this->assertArrayHasKey('Double', $messages);
        $this->assertContains('Double Message', $messages['Double']);

        $this->assertArrayHasKey('Single', $messages);
        $this->assertContains('Single Message', $messages['Single']);

        $this->assertArrayHasKey('Mixed', $messages);
        $this->assertContains('Mixed Message', $messages['Mixed']);

        unlink($jsFile);
    }

    public function testMultipleTranslatorFunctions()
    {
        $jsFile = Yii::getAlias('@runtime/test_multiple_translators.js');
        $content = "
            i18n.t('Cat1', 'Msg1');
            t('Cat2', 'Msg2');
        ";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, ['i18n.t', 't']);

        $this->assertArrayHasKey('Cat1', $messages);
        $this->assertContains('Msg1', $messages['Cat1']);

        $this->assertArrayHasKey('Cat2', $messages);
        $this->assertContains('Msg2', $messages['Cat2']);

        unlink($jsFile);
    }

    public function testIgnoreCategories()
    {
        $jsFile = Yii::getAlias('@runtime/test_ignore.js');
        $content = "
            i18n.t('IgnoreMe', 'Msg1');
            i18n.t('Stay', 'Msg2');
            i18n.t('Wildcard.Sub', 'Msg3');
            i18n.t('Wildcard', 'Msg4');
        ";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, 'i18n.t', ['IgnoreMe', 'Wildcard.*']);

        $this->assertArrayNotHasKey('IgnoreMe', $messages);
        $this->assertArrayHasKey('Stay', $messages);
        $this->assertArrayNotHasKey('Wildcard.Sub', $messages);
        $this->assertArrayHasKey('Wildcard', $messages);

        unlink($jsFile);
    }

    public function testUnescapeJsString()
    {
        $jsFile = Yii::getAlias('@runtime/test_unescape.js');
        $content = "
            i18n.t('Cat', 'Line1\\nLine2');
            i18n.t('Cat', 'Escaped \\'Quote\\'');
            i18n.t('Cat', 'Backslash \\\\');
        ";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, 'i18n.t');

        $this->assertContains("Line1\nLine2", $messages['Cat']);
        $this->assertContains("Escaped 'Quote'", $messages['Cat']);
        $this->assertContains("Backslash \\", $messages['Cat']);

        unlink($jsFile);
    }

    public function testMultilineMessage()
    {
        $jsFile = Yii::getAlias('@runtime/test_multiline.js');
        $content = "
            i18n.t('Cat', 'Multi
line');
        ";
        file_put_contents($jsFile, $content);

        $messages = $this->extractor->extract($jsFile, 'i18n.t');

        $this->assertArrayHasKey('Cat', $messages);
        $this->assertContains("Multi\nline", $messages['Cat']);

        unlink($jsFile);
    }
}

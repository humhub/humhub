<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class RichTextLinkExtensionTest extends HumHubDbTestCase
{
    /*
     * Links
     */

    // TODO: test RichTextLinKExtensionMatch with addition (e.g. image size)

    public function testParseSimpleLinkExtension()
    {
        $text = '[Text](my-extension:extensionId "title")';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEquals('title', $matches[0]->getTitle());
        static::assertEmpty($matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithEmptyText()
    {
        $text = '[](my-extension:extensionId "title")';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEmpty($matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEquals('title', $matches[0]->getTitle());
        static::assertEmpty($matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithEmptyExtensionId()
    {
        $text = '[Text](my-extension: "title")';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEmpty($matches[0]->getExtensionId());
        static::assertEquals('title', $matches[0]->getTitle());
        static::assertEmpty($matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithEmptyTitle()
    {
        $text = '[Text](my-extension:extensionId "")';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEmpty($matches[0]->getTitle());
        static::assertEmpty($matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithoutTitle()
    {
        $text = '[Text](my-extension:extensionId)';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEmpty($matches[0]->getTitle());
        static::assertEmpty($matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithtAddition()
    {
        $text = '[Text](my-extension:extensionId "title" 150x150)';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEquals('title', $matches[0]->getTitle());
        static::assertEquals('150x150', $matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithoutTitleAndWithAddition()
    {
        $text = '[Text](my-extension:extensionId 150x150)';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEquals('extensionId', $matches[0]->getExtensionId());
        static::assertEmpty($matches[0]->getTitle());
        static::assertEquals('150x150', $matches[0]->getAddition());
    }

    public function testParseLinkExtensionWithoutTitleAndExtensionIdAndWithAddition()
    {
        $text = '[Text](my-extension: 150x150)';
        $matches = RichTextLinkExtension::scanLinkExtension($text, 'my-extension');
        static::assertCount(1, $matches);
        static::assertEquals('Text', $matches[0]->getText());
        static::assertEquals('my-extension', $matches[0]->getExtensionKey());
        static::assertEmpty($matches[0]->getExtensionId());
        static::assertEmpty($matches[0]->getTitle());
        static::assertEquals('150x150', $matches[0]->getAddition());
    }

    public function testExtensionTargetUrl()
    {
        static::assertEquals('https://example.com/a?b=c', RichTextLinkExtension::getExtensionTargetUrl('my-extension:https://example.com/a?b=c'));
        static::assertEquals('http://example.com', RichTextLinkExtension::getExtensionTargetUrl('my-extension:http://example.com'));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl('my-extension:some-id'));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl('https://example.com'));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl('javascript:https://example.com'));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl('mailto:https://example.com'));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl(''));
        static::assertNull(RichTextLinkExtension::getExtensionTargetUrl(null));

        foreach (RichTextLinkExtension::NON_EXTENSION_SCHEMES as $scheme) {
            static::assertNull(RichTextLinkExtension::getExtensionTargetUrl(strtoupper($scheme) . ':https://example.com'));
        }
    }

    public function testNonExtensionSchemesMatchTheEditor()
    {
        $source = file_get_contents(Yii::getAlias('@humhub/resources/js/richtext/core/util/linkUtil.js'));
        static::assertSame(1, preg_match('/NON_EXTENSION_SCHEMES = \[([^\]]+)\]/', $source, $match));

        static::assertSame(
            RichTextLinkExtension::NON_EXTENSION_SCHEMES,
            array_map(static fn($scheme) => trim($scheme, " '"), explode(',', $match[1])),
        );
    }

    public function testBuildSimpleLinkExtensionString()
    {
        $result = MentioningExtension::buildExtensionLink('Text', 'extensionId', 'title');
        static::assertEquals('[Text](mention:extensionId "title")', $result);
    }

    public function testBuildSimpleLinkExtensionStringWithAddition()
    {
        $result = MentioningExtension::buildExtensionLink('Text', 'extensionId', 'title', '150x150');
        static::assertEquals('[Text](mention:extensionId "title" 150x150)', $result);
    }

    public function testBuildSimpleLinkExtensionStringWithoutTitleAndWithAddition()
    {
        $result = MentioningExtension::buildExtensionLink('Text', 'extensionId', null, '150x150');
        static::assertEquals('[Text](mention:extensionId 150x150)', $result);
    }

}

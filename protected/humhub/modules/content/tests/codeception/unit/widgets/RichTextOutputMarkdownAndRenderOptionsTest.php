<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\helpers\Html;
use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Covers `RichText::outputMarkdownAndRenderOptions()`, the client-render counterpart of
 * `RichText::output()` added so the comment JSON payload can ship raw markdown + a plain
 * render-options object instead of a pre-built HTML envelope string - see
 * docs/develop/ui-js-vuejs-interop.md, "RichTextOutput".
 */
class RichTextOutputMarkdownAndRenderOptionsTest extends HumHubDbTestCase
{
    public function testEmptyTextReturnsEmptyMarkdownAndNoOptions()
    {
        $result = RichText::outputMarkdownAndRenderOptions('');

        $this->assertSame(['markdown' => '', 'options' => []], $result);
    }

    public function testPlainTextIsReturnedVerbatimWithStaticConfigOptions()
    {
        $result = RichText::outputMarkdownAndRenderOptions('Hello world');

        $this->assertSame('Hello world', $result['markdown']);
        $this->assertSame([], $result['options']['exclude']);
        $this->assertSame([], $result['options']['include']);
        $this->assertSame([], $result['options']['plugin-options']);
        $this->assertFalse($result['options']['edit']);
        $this->assertTrue($result['options']['ui-richtext']);
        $this->assertSame('ui.richtext.prosemirror.RichText', $result['options']['ui-widget']);
        $this->assertTrue($result['options']['ui-init']);
        $this->assertArrayNotHasKey('preset', $result['options']);
        $this->assertArrayNotHasKey('oembeds', $result['options']);
    }

    /**
     * The markdown text must NOT be HTML-encoded (that only happens for the HTML envelope
     * `run()`/`output()` builds) - the client's own `{{ message }}` text interpolation
     * (never `v-html`) is what makes a literal `<script>` land inert, and double-encoding
     * here would corrupt genuine markdown (e.g. literal `&`/`<` a user typed).
     */
    public function testMarkdownIsNotHtmlEncoded()
    {
        $result = RichText::outputMarkdownAndRenderOptions('<script>alert(1)</script> & more');

        $this->assertSame('<script>alert(1)</script> & more', $result['markdown']);
    }

    public function testMentioningIsResolvedInTheMarkdownExactlyLikeOutput()
    {
        $user = User::findOne(['username' => 'User1']);
        $text = '[' . $user->displayName . '](mention:' . $user->guid . ' "' . $user->getUrl() . '")';

        $result = RichText::outputMarkdownAndRenderOptions($text);
        $htmlOutput = RichText::output($text);

        // Same mentioning-resolution pipeline as output() (onBeforeOutput) - both must agree,
        // proving getMarkdownAndRenderOptions() cannot drift from output()'s own text. The raw
        // markdown carries the literal url; output()'s HTML envelope carries the SAME url but
        // HTML-encoded (run()'s Html::encode($output), which getMarkdown() deliberately skips -
        // see testMarkdownIsNotHtmlEncoded()).
        $this->assertStringContainsString($user->getUrl(), $result['markdown']);
        $this->assertStringContainsString(Html::encode($user->getUrl()), $htmlOutput);
    }

    public function testOembedPreviewsAreShippedInOptionsKeyedByUrlInsteadOfAppendedHtml()
    {
        $url = 'https://www.youtube.com/watch?v=render-options-test';
        $this->assertTrue((new UrlOembed(['url' => $url, 'preview' => '<iframe>preview</iframe>']))->save());

        $text = '[' . $url . '](oembed:' . $url . ')';
        $result = RichText::outputMarkdownAndRenderOptions($text);

        $this->assertArrayHasKey('oembeds', $result['options']);
        $this->assertSame(['<iframe>preview</iframe>'], array_values($result['options']['oembeds']));
        $this->assertSame('<iframe>preview</iframe>', $result['options']['oembeds'][$url]);

        // The HTML envelope path no longer needs to carry the preview fragment appended
        // after it for this to work - not a regression check on output() itself (still
        // covered by RichTextOembedTest), just documenting the split.
    }

    public function testNoOembedsKeyWhenMessageHasNoEmbeddableLinks()
    {
        $result = RichText::outputMarkdownAndRenderOptions('Just plain text, no links at all');

        $this->assertArrayNotHasKey('oembeds', $result['options']);
    }

    /**
     * Sanity check: the record-aware entry point CommentSerializer actually uses.
     */
    public function testAcceptsARecordConfigLikeOutputDoes()
    {
        $post = Post::findOne(['id' => 1]);

        $result = RichText::outputMarkdownAndRenderOptions('Hello record', ['record' => $post]);

        $this->assertSame('Hello record', $result['markdown']);
    }
}

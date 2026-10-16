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
use Yii;

/**
 * Covers `RichText::outputMarkdown()`, the processed markdown without the HTML envelope
 * `RichText::output()` builds - what the API payloads ship so a client renders the text
 * itself (see docs/develop/ui-js-vuejs-interop.md, "RichTextOutput").
 */
class RichTextOutputMarkdownTest extends HumHubDbTestCase
{
    public function testEmptyTextIsReturnedAsIs()
    {
        $this->assertSame('', RichText::outputMarkdown(''));
        $this->assertNull(RichText::outputMarkdown(null));
    }

    public function testPlainTextIsReturnedVerbatim()
    {
        $this->assertSame('Hello world', RichText::outputMarkdown('Hello world'));
    }

    /**
     * The markdown must NOT be HTML-encoded (that only happens for the HTML envelope
     * `run()`/`output()` builds) - the client's own `{{ message }}` text interpolation
     * (never `v-html`) is what makes a literal `<script>` land inert, and encoding here
     * would corrupt genuine markdown (e.g. a literal `&`/`<` a user typed).
     */
    public function testMarkdownIsNotHtmlEncoded()
    {
        $this->assertSame(
            '<script>alert(1)</script> & more',
            RichText::outputMarkdown('<script>alert(1)</script> & more'),
        );
    }

    public function testMentioningIsResolvedExactlyLikeOutput()
    {
        $user = User::findOne(['username' => 'User1']);
        $text = '[' . $user->displayName . '](mention:' . $user->guid . ' "' . $user->getUrl() . '")';

        $markdown = RichText::outputMarkdown($text);
        $htmlOutput = RichText::output($text);

        // Same onBeforeOutput() pipeline as output(), so the two cannot drift: the markdown
        // carries the literal url, output()'s envelope the same url HTML-encoded.
        $this->assertStringContainsString($user->getUrl(), $markdown);
        $this->assertStringContainsString(Html::encode($user->getUrl()), $htmlOutput);
    }

    /**
     * An oembed link stays in the markdown as it is - the preview is fetched by the client
     * (`humhub.oembed.js`), because it depends on the reader: with the consent setting on, a
     * member who has not allowed the domain gets the confirmation prompt where a guest gets
     * the media. The markdown therefore has to be identical for both, while output() (which
     * still ships the previews, now fetched in onAfterOutput()) legitimately differs.
     */
    public function testOembedLinksStayInTheMarkdownForEveryReader()
    {
        $url = 'https://www.youtube.com/watch?v=output-markdown-test';
        $this->assertTrue((new UrlOembed(['url' => $url, 'preview' => '<iframe class="oembed_snippet">preview</iframe>']))->save());
        Yii::$app->settings->set('oembed.requestConfirmation', true);

        $text = '[' . $url . '](oembed:' . $url . ')';

        Yii::$app->user->logout();
        $asGuest = RichText::outputMarkdown($text);
        $guestHtml = RichText::output($text);

        $this->becomeUser('User1');
        $asMember = RichText::outputMarkdown($text);
        $memberHtml = RichText::output($text);

        $this->assertSame($text, $asGuest);
        $this->assertSame($text, $asMember);

        $this->assertStringContainsString('<iframe class="oembed_snippet">preview</iframe>', $guestHtml);
        $this->assertStringContainsString('oembed_confirmation', $memberHtml);
        $this->assertStringNotContainsString('<iframe class="oembed_snippet">preview</iframe>', $memberHtml);
    }

    /**
     * Sanity check: the record-aware entry point the serializers actually use.
     */
    public function testAcceptsARecordConfigLikeOutputDoes()
    {
        $post = Post::findOne(['id' => 1]);

        $this->assertSame('Hello record', RichText::outputMarkdown('Hello record', ['record' => $post]));
    }
}

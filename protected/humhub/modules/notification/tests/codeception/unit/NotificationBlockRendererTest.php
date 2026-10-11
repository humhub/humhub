<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationBlockRenderer;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class NotificationBlockRendererTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testHeadingAndTextAreEncoded()
    {
        $renderer = $this->renderer();

        $heading = NotificationBlock::heading(' Release <2.0> & more ');
        $this->assertStringContainsString('>Release &lt;2.0&gt; &amp; more</p>', $this->html($renderer, $heading));
        $this->assertSame('Release <2.0> & more', $this->text($renderer, $heading));

        $text = NotificationBlock::text("  <b>Hi</b>\nthere  ");
        $this->assertStringContainsString(">&lt;b&gt;Hi&lt;/b&gt;<br />\nthere</p>", $this->html($renderer, $text));
        $this->assertSame("<b>Hi</b>\nthere", $this->text($renderer, $text));
    }

    public function testQuoteWithAuthorIsACard()
    {
        $author = User::findOne(['id' => 2]);
        $block = NotificationBlock::quote("Let <b>me</b> in!\nPlease", $author, '2026-10-10 10:00:00');
        $renderer = $this->renderer();

        $html = $this->html($renderer, $block);
        $this->assertStringContainsString("Let &lt;b&gt;me&lt;/b&gt; in!<br />\nPlease", $html);
        $this->assertStringNotContainsString('<b>me</b>', $html);
        $this->assertStringContainsString(Html::encode($author->displayName), $html);
        $this->assertStringContainsString($author->createUrl('/user/profile', [], true), $html);
        $this->assertStringContainsString('<time', $html);

        $this->assertSame($author->displayName . ":\n> Let <b>me</b> in!\n> Please", $this->text($renderer, $block));
    }

    public function testQuoteWithoutAuthor()
    {
        $block = NotificationBlock::quote('A <note>');
        $renderer = $this->renderer();

        $html = $this->html($renderer, $block);
        $this->assertStringContainsString('A &lt;note&gt;', $html);
        $this->assertStringNotContainsString('<time', $html);
        $this->assertSame('> A <note>', $this->text($renderer, $block));
    }

    public function testRichTextGoesThroughTheConverters()
    {
        $block = NotificationBlock::richText("Read **this** [article](https://www.humhub.org/news)\n\n![Logo](https://www.humhub.org/logo.png)");
        $renderer = $this->renderer();

        $html = $this->html($renderer, $block);
        $this->assertStringContainsString('<strong>this</strong>', $html);
        $this->assertStringContainsString('href="https://www.humhub.org/news"', $html);
        $this->assertMatchesRegularExpression('/<img[^>]+src="https:\/\/www\.humhub\.org\/logo\.png"[^>]+style="[^"]*max-width/', $html);
        // limited to the mail's width
        $this->assertStringContainsString('max-width: 560px', $html);
        $this->assertStringContainsString('table-layout: fixed', $html);

        $text = $this->text($renderer, $block);
        $this->assertStringContainsString('this', $text);
        $this->assertStringContainsString('article', $text);
        $this->assertStringNotContainsString('**', $text);
        $this->assertStringNotContainsString('<', $text);
    }

    public function testHtmlOnlyInTheHtmlMail()
    {
        $block = NotificationBlock::html('<table><tr><td style="color: red">Red</td></tr></table>', 'Red as text');
        $renderer = $this->renderer();

        $this->assertSame('<table><tr><td style="color: red">Red</td></tr></table>', $this->html($renderer, $block));
        $this->assertSame('Red as text', $this->text($renderer, $block));
    }

    public function testContentPreview()
    {
        $post = Post::findOne(['id' => 1]);
        $renderer = $this->renderer();
        $block = NotificationBlock::contentPreview($post);

        $html = $this->html($renderer, $block);
        $this->assertStringContainsString(Html::encode(User::findOne(['id' => 2])->displayName), $html);
        $this->assertStringContainsString(strip_tags((string)$post->message), strip_tags($html));
        $this->assertStringContainsString(trim(strip_tags((string)$post->message)), $this->text($renderer, $block));
    }

    public function testConsecutiveButtonsFormOneRow()
    {
        $renderer = $this->renderer();
        $blocks = [
            NotificationBlock::button('A <1>', 'https://example.com/a?x=1&y=2'),
            NotificationBlock::button('B', 'https://example.com/b'),
            NotificationBlock::text('Between'),
            NotificationBlock::button('C', 'https://example.com/c'),
        ];

        $html = $renderer->renderMailHtml($blocks);
        $this->assertCount(3, $html);
        $this->assertStringContainsString('A &lt;1&gt;', $html[0]);
        $this->assertStringContainsString('href="https://example.com/a?x=1&amp;y=2"', $html[0]);
        $this->assertStringContainsString('https://example.com/b', $html[0]);
        $this->assertStringContainsString('Between', $html[1]);
        $this->assertStringContainsString('https://example.com/c', $html[2]);

        $this->assertSame([
            "A <1>: https://example.com/a?x=1&y=2\nB: https://example.com/b",
            'Between',
            'C: https://example.com/c',
        ], $renderer->renderMailText($blocks));
    }

    public function testEmptyTextPartsAreLeftOut()
    {
        $this->assertSame(['x'], $this->renderer()->renderMailText([NotificationBlock::text('  '), NotificationBlock::text('x')]));
    }

    private function html(NotificationBlockRenderer $renderer, NotificationBlock $block): string
    {
        $parts = $renderer->renderMailHtml([$block]);
        $this->assertCount(1, $parts);

        return $parts[0];
    }

    private function text(NotificationBlockRenderer $renderer, NotificationBlock $block): string
    {
        return $renderer->renderMailText([$block])[0] ?? '';
    }

    private function renderer(): NotificationBlockRenderer
    {
        return new NotificationBlockRenderer($this->notification());
    }

    private function notification(): BaseNotification
    {
        $post = Post::findOne(['id' => 1]);
        $record = new Notification([
            'class' => TestContentNotification::class,
            'user_id' => 1,
            'originator_id' => 2,
            'content_id' => $post->content->id,
            'contentcontainer_id' => $post->content->contentcontainer_id,
        ]);
        $this->assertTrue($record->save());

        return NotificationManager::load($record);
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\MessageFormat;
use humhub\modules\notification\components\MessageParam;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class MessageParamTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testTextIsEncodedForHtmlOnly()
    {
        $param = MessageParam::text('Tom & <Jerry>');
        $this->assertSame('Tom &amp; &lt;Jerry&gt;', $param->render(MessageFormat::Html));
        $this->assertSame('Tom & <Jerry>', $param->render(MessageFormat::Text));
    }

    public function testEmphasisIsStrongForHtmlAndQuotedForText()
    {
        $param = MessageParam::emphasis('Sales & <Marketing>');
        $this->assertSame('<strong>Sales &amp; &lt;Marketing&gt;</strong>', $param->render(MessageFormat::Html));
        $this->assertSame('“Sales & <Marketing>”', $param->render(MessageFormat::Text));
    }

    public function testUserIsStrongForHtmlAndPlainForText()
    {
        $user = User::findOne(['id' => 1]);
        $param = MessageParam::user($user);
        $this->assertSame('<strong>' . htmlspecialchars((string)$user->displayName) . '</strong>', $param->render(MessageFormat::Html));
        $this->assertSame($user->displayName, $param->render(MessageFormat::Text));
    }

    public function testTruncatesBeforeEncoding()
    {
        $this->assertSame('abcd…', MessageParam::text('abcdefgh', 5)->render(MessageFormat::Text));
        $this->assertSame('&amp;&amp;…', MessageParam::text('&&&&&', 3)->render(MessageFormat::Html));
        $this->assertSame('“abcd…”', MessageParam::emphasis('abcdefgh', 5)->render(MessageFormat::Text));
        $this->assertSame('abcde', MessageParam::text('abcde', 5)->render(MessageFormat::Text), 'not longer than the limit');
        $this->assertSame('äöü…', MessageParam::text('äöüßäöü', 4)->render(MessageFormat::Text), 'multibyte safe');
    }

    public function testHtmlKeepsItsMarkup()
    {
        $param = MessageParam::html('<em>x</em> &amp; y', '*x* & y');
        $this->assertSame('<em>x</em> &amp; y', $param->render(MessageFormat::Html));
        $this->assertSame('*x* & y', $param->render(MessageFormat::Text));
    }

    public function testRenderAll()
    {
        $params = [
            'name' => 'A & B',
            'title' => MessageParam::emphasis('T'),
            'count' => 3,
        ];

        $this->assertSame(['name' => 'A &amp; B', 'title' => '<strong>T</strong>', 'count' => 3], MessageParam::renderAll($params, MessageFormat::Html));
        $this->assertSame(['name' => 'A & B', 'title' => '“T”', 'count' => 3], MessageParam::renderAll($params, MessageFormat::Text));
    }

    public function testContentIsTypeAndPreview()
    {
        $post = $this->createPost('Tom & <Jerry> are friends');

        $param = MessageParam::content($post);
        $this->assertSame('post "Tom &amp; &lt;Jerry&gt; are friends"', $param->render(MessageFormat::Html));
        $this->assertSame('post "Tom & <Jerry> are friends"', $param->render(MessageFormat::Text));
        $this->assertSame('<strong>post "Tom &amp; &lt;Jerry&gt; are friends"</strong>', $param->render(MessageFormat::Html, emphasizeContent: true));
        $this->assertSame('post "Tom & <Jerry>..."', $param->render(MessageFormat::Text, 13));
        $this->assertSame('Tom & <Jerry> are friends', MessageParam::content($post, withType: false)->render(MessageFormat::Text));
    }

    public function testRedactedContentIsTheType()
    {
        $param = MessageParam::content($this->createPost('Secret plans'));
        $this->assertSame('post', $param->render(MessageFormat::Text, redacted: true));
        $this->assertSame('<strong>post</strong>', $param->render(MessageFormat::Html, emphasizeContent: true, redacted: true));
    }

    public function testRenderRedactedKeepsNamesNumbersAndContentTypes()
    {
        $params = [
            'user' => MessageParam::user(User::findOne(['id' => 1])),
            'content' => MessageParam::content($this->createPost('Secret plans')),
            'title' => MessageParam::emphasis('Secret title'),
            'count' => 2,
            'empty' => '',
        ];
        $message = static fn(string $sentence): callable => static fn(array $p): string => strtr($sentence, [
            '{user}' => $p['user'], '{content}' => $p['content'], '{title}' => $p['title'], '{count}' => $p['count'], '{empty}' => $p['empty'],
        ]);

        $this->assertSame(
            'Admin Tester commented your post (2)',
            MessageParam::renderRedacted($message('{user} commented your {content} ({count}){empty}'), $params, MessageFormat::Text),
            'a value the sentence does not use does not matter',
        );
        $this->assertNull(MessageParam::renderRedacted($message('{user} commented {title}'), $params, MessageFormat::Text));
        $this->assertNull(MessageParam::renderRedacted(
            static fn(array $p): string => $p['user'] . ' wrote ' . $p['plain'],
            $params + ['plain' => 'a plain string'],
            MessageFormat::Text,
        ));
        $this->assertNull(MessageParam::renderRedacted(
            static fn(array $p): string => 'Created ' . $p['title'],
            ['title' => MessageParam::content($this->createPost('Secret plans'), withType: false)],
            MessageFormat::Text,
        ), 'the preview alone is content');
    }

    private function createPost(string $message): Post
    {
        $this->becomeUser('User2');
        $post = new Post(Space::findOne(['id' => 1]), ['message' => $message]);
        $this->assertTrue($post->save());

        return $post;
    }
}

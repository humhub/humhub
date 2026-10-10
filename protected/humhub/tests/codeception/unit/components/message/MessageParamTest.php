<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\message;

use humhub\components\message\MessageFormat;
use humhub\components\message\MessageParam;
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
}

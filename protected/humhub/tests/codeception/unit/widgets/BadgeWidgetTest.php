<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Link;
use tests\codeception\_support\HumHubDbTestCase;

class BadgeWidgetTest extends HumHubDbTestCase
{
    private const XSS_LABEL = '<img src=x onerror=alert(1)>';
    private const XSS_LABEL_ENCODED = '&lt;img src=x onerror=alert(1)&gt;';

    public function testLabelIsEncodedByDefault()
    {
        $this->assertStringContainsString(
            self::XSS_LABEL_ENCODED,
            (string)Badge::none(self::XSS_LABEL),
        );
    }

    /**
     * The link added by action() wraps the rendered badge, so it must not encode its label again.
     */
    public function testActionKeepsTheBadgeMarkupUnescaped()
    {
        $result = (string)Badge::none('3')->icon('comment-o')->action('comment.toggleComment', null, '#comment_C1P');

        $this->assertStringContainsString('data-action-click="comment.toggleComment"', $result);
        $this->assertStringContainsString('data-action-click-target="#comment_C1P"', $result);
        $this->assertMatchesRegularExpression('/<a [^>]*>\s*<span [^>]*class="badge"/', $result);
        $this->assertStringNotContainsString('&lt;span', $result);
    }

    /**
     * The badge label stays encoded even once the badge is wrapped in a link.
     */
    public function testActionKeepsTheLabelEncoded()
    {
        $result = (string)Badge::none(self::XSS_LABEL)->action('foo');

        $this->assertStringContainsString(self::XSS_LABEL_ENCODED, $result);
        $this->assertStringNotContainsString(self::XSS_LABEL, $result);
    }

    public function testWithLinkKeepsTheBadgeMarkupUnescapedAndTheLabelEncoded()
    {
        $result = (string)Badge::none(self::XSS_LABEL)->withLink(Link::withAction(null, 'foo'));

        $this->assertMatchesRegularExpression('/<a [^>]*>\s*<span [^>]*class="badge"/', $result);
        $this->assertStringContainsString(self::XSS_LABEL_ENCODED, $result);
        $this->assertStringNotContainsString(self::XSS_LABEL, $result);
    }

    /**
     * The label of a link given to withLink() is replaced by the rendered badge, so it never reaches the output.
     */
    public function testWithLinkDiscardsTheLabelOfTheGivenLink()
    {
        $result = (string)Badge::none('safe')->withLink(Link::to(self::XSS_LABEL));

        $this->assertStringContainsString('>safe<', $result);
        $this->assertStringNotContainsString(self::XSS_LABEL, $result);
        $this->assertStringNotContainsString(self::XSS_LABEL_ENCODED, $result);
    }

    public function testLabelIsNotEncodedWhenExplicitlyDisabled()
    {
        $badge = new Badge(['label' => '<b>markup</b>', 'encodeLabel' => false]);

        $this->assertStringContainsString('<b>markup</b>', (string)$badge);
    }
}

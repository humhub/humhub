<?php

namespace tests\codeception\unit\modules\space;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The membership request response replaces the membership button on the client. The
 * rendered markup is handed to JavaScript, so it must be embedded as an encoded string
 * literal instead of being interpolated into a quoted literal.
 */
class RequestMembershipSaveViewTest extends HumHubDbTestCase
{
    private function renderSaveView($spaceId, string $buttonHtml): string
    {
        return Yii::$app->getView()->renderFile(
            Yii::getAlias('@humhub/modules/space/views/membership/requestMembershipSave.php'),
            ['spaceId' => $spaceId, 'newMembershipButton' => $buttonHtml],
        );
    }

    /**
     * Returns the argument the save view passes to replaceWith(), as it appears in the source.
     */
    private function getReplaceWithArgument(string $script): string
    {
        $this->assertSame(1, preg_match('/\.replaceWith\((.*)\);/', $script, $matches));

        return $matches[1];
    }

    /**
     * Neither a quote nor a closing script tag may terminate the surrounding context.
     */
    public function testButtonMarkupIsEncodedForJavaScript()
    {
        $script = $this->renderSaveView(1, '<a title="\'">x</a></script><img src=x onerror=alert(1)>');
        $argument = $this->getReplaceWithArgument($script);

        // Angle brackets and quotes are hex escaped, so neither the string literal nor
        // the script element can be terminated by the markup.
        $this->assertStringNotContainsString('<', $argument);
        $this->assertStringNotContainsString('>', $argument);
        $this->assertStringNotContainsString("'", $argument);
        $this->assertStringNotContainsString('"', substr($argument, 1, -1));
    }

    /**
     * The button markup still arrives unchanged at replaceWith() once the JavaScript
     * string literal is evaluated.
     */
    public function testEncodedButtonMarkupDecodesToTheOriginalMarkup()
    {
        $buttonHtml = '<a href="#" class="btn btn-accent active" title="Pending">Pending</a>';
        $argument = $this->getReplaceWithArgument($this->renderSaveView(1, $buttonHtml));

        $this->assertSame($buttonHtml, json_decode($argument, true));
    }

    /**
     * The space id is interpolated into a selector and must always be numeric.
     */
    public function testSpaceIdIsCastToInt()
    {
        $script = $this->renderSaveView('1"]).x(', '');

        $this->assertStringContainsString('[data-space-request-membership=1]', $script);
        $this->assertStringNotContainsString('.x(', $script);
    }
}

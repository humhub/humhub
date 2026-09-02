<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\widgets;

use humhub\modules\comment\widgets\CommentFormShell;
use humhub\modules\post\models\Post;
use humhub\widgets\VueFormShell;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Pins the comment-specific field composition (the richtext editor) on top of the
 * generic {@see VueFormShell} mechanism: the shell is rendered ONCE and cloned per form instance
 * client-side by string-replacing the `__VUEFORM__` token (see `VueFormShell`'s own class
 * docblock for the general contract), so every id the shell declares OR references must carry
 * it - otherwise two clones on the same page would collide.
 */
class CommentFormShellTest extends HumHubDbTestCase
{
    public function testEveryIdAndForAttributeContainsTheToken()
    {
        $html = $this->renderShell();

        $this->assertGreaterThan(0, preg_match_all('/\b(?:id|for)="([^"]*)"/', $html, $matches));

        foreach ($matches[1] as $value) {
            $this->assertStringContainsString(
                VueFormShell::TOKEN,
                $value,
                'id/for attribute "' . $value . '" does not carry the shell token',
            );
        }
    }

    public function testEveryHashDataAttributeContainsTheToken()
    {
        $html = $this->renderShell();

        $this->assertGreaterThan(0, preg_match_all('/\bdata-[a-z-]+="(#[^"]*)"/', $html, $matches));

        foreach ($matches[1] as $value) {
            $this->assertStringContainsString(
                VueFormShell::TOKEN,
                $value,
                'CSS-id-selector data attribute "' . $value . '" does not carry the shell token',
            );
        }
    }

    public function testHasNoServerRenderedUploadStack()
    {
        $html = $this->renderShell();

        // Uploads are a native field now (`UploadField.vue`, rendered by CommentForm.vue), so
        // the shell carries neither the upload button, nor the file preview, nor the progress
        // container it used to compose - only the richtext editor's own (hidden) upload input
        // for in-editor image insertion remains, which is part of the RichTextField widget.
        $this->assertStringNotContainsString('vueform-upload', $html);
        $this->assertStringNotContainsString('data-upload-submit-name="Comment[fileList][]"', $html);
        $this->assertStringNotContainsString('file-preview', $html);
    }

    public function testRendersTheButtonRowTheIslandTeleportsInto()
    {
        $html = $this->renderShell();

        // CommentForm.vue moves the upload trigger and the submit button in here - an empty
        // container in the server-rendered shell, but its absence would silently make both
        // render at the wrong place (Teleport falls back to rendering in place).
        $this->assertStringContainsString('class="richtext-create-buttons"', $html);
    }

    public function testHasNoSubmitButton()
    {
        $html = $this->renderShell();

        // The island owns submission (CommentForm.vue posts the JSON API directly) - a
        // native submit button would be at best redundant, at worst let a click through
        // before JS has bound its own handler.
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*type="submit"/', $html);
    }

    public function testHasNoCsrfInput()
    {
        $html = $this->renderShell();

        // The shell is rendered once and cloned client-side - a baked-in CSRF token would
        // go stale for every clone and is never read (the island posts through the
        // ordinary ajax pipeline, which attaches a live token itself). See
        // widgets/views/commentFormShell.php for the `'csrf' => false` option this pins.
        $this->assertStringNotContainsString('_csrf', $html);
    }

    private function renderShell(): string
    {
        $this->becomeUser('User2');

        return CommentFormShell::widget(['content' => Post::findOne(['id' => 11])->content]);
    }
}

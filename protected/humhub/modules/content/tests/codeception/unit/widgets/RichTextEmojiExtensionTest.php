<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\richtext\extensions\emoji\RichTextEmojiExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichTextEditor;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Rich texts saved before 1.20 store emojis as shortcodes, converted on read and written back as Unicode on save
 */
class RichTextEmojiExtensionTest extends HumHubDbTestCase
{
    /**
     * @dataProvider legacyShortcodes
     */
    public function testConvertsLegacyShortcodes(string $text, string $expected)
    {
        $this->assertSame($expected, RichTextEmojiExtension::convertEmojiToUtf8($text));
    }

    public static function legacyShortcodes(): array
    {
        return [
            'name of the picker' => [':grinning face: :thumbs up:', '😀 👍'],
            'legacy name' => [':smile: :slightly_smiling_face: :+1: :-1: :heart:', '😄 🙂 👍 👎 ❤️'],
            'editor before 1.3' => [';smile; ;heart_eyes;', '😄 😍'],
            'markdown escape of the editor' => [':keycap \\*: :keycap #:', '*️⃣ #️⃣'],
            'quotes in the name' => [':Japanese “free of charge” button:', '🈚'],
            'unknown name' => [':not_an_emoji: and :smile:', ':not_an_emoji: and 😄'],
            'colons of other text' => ['10:30: a:foo:smile:', '10:30: a:foo😄'],
            'no emoji' => ['Hello: world; again', 'Hello: world; again'],
        ];
    }

    public function testKeepsCode()
    {
        $text = "`:smile:` and ``a :smile: ``\n\n```\n:smile:\n```\n\n~~~\n;smile;\n~~~\n\n:smile:";

        $this->assertSame(
            "`:smile:` and ``a :smile: ``\n\n```\n:smile:\n```\n\n~~~\n;smile;\n~~~\n\n😄",
            RichTextEmojiExtension::convertEmojiToUtf8($text),
        );
    }

    public function testKeepsUnclosedCodeBlock()
    {
        $this->assertSame("😄\n```\n:smile:", RichTextEmojiExtension::convertEmojiToUtf8(":smile:\n```\n:smile:"));
    }

    public function testOutput()
    {
        $this->assertStringContainsString('Hi 😄', RichText::output('Hi :smile:'));
        $this->assertSame('Hi 😄', RichText::outputMarkdown('Hi :smile:'));
        $this->assertSame('Hi 😄', RichText::convert('Hi :smile:', RichText::FORMAT_PLAINTEXT));
    }

    public function testEditorStartsWithUnicode()
    {
        $this->assertStringContainsString('Hi 😄', ProsemirrorRichTextEditor::widget([
            'name' => 'message',
            'value' => 'Hi :smile:',
        ]));
    }

    public function testSaveWritesUnicode()
    {
        $post = Post::findOne(['id' => 1]);
        $post->updateAttributes(['message' => 'Hi :smile: `:smile:`']);

        RichText::postProcess($post->message, $post, 'message');

        $this->assertSame('Hi 😄 `:smile:`', Post::findOne(['id' => 1])->message);
    }
}

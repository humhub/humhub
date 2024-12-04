<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class RichTextEmailHtmlConverterTest extends HumHubDbTestCase
{
    public function testConvertLinkToHtml()
    {
        $this->assertConversionResult(
            'Test[Link](https://www.humhub.com/de)Test',
            '<p>Test<a href="https://www.humhub.com/de" target="_blank" rel="nofollow noreferrer noopener"> Link </a>Test</p>',
        );
    }

    public function testConvertLinkAsTextToHtml()
    {
        $this->assertConversionResult(
            'Test[Link](https://www.humhub.com/de)Test',
            '<p>Test Link Test</p>',
            [
                RichTextToHtmlConverter::OPTION_LINK_AS_TEXT => true,
            ],
        );
    }

    public function testConvertImageToHtml()
    {
        $admin = $this->becomeUser('Admin');

        $file = new File();
        $file->file_name = 'test_image.jpg';
        $file->save();

        $token = DownloadAction::generateDownloadToken($file, $admin);

        $this->assertConversionResult(
            'Test![' . $file->file_name . '](file-guid:' . $file->guid . ' "' . $file->file_name . '")Test',
            '<p>Test<img src="http://localhost/index-test.php?r=file%2Ffile%2Fdownload&amp;guid=' . $file->guid . '&amp;hash_sha1=&amp;token=' . $token . '" alt="test_image.jpg" style="max-width: 100%;">Test</p>',
            [
                RichTextToEmailHtmlConverter::OPTION_RECEIVER_USER => $admin,
            ],
        );

        $this->assertConversionResult(
            '![](http://local/image.jpg)',
            '<p><img src="http://local/image.jpg" alt="" style="max-width: 100%;"></p>',
        );

        $this->assertConversionResult(
            '![Alt text><](http://local/image.jpg "Description text" =200x100)',
            '<p><img class="center-block" src="http://local/image.jpg" width="200" height="100" alt="Alt text" title="Description text" style="max-width: 100%; display: block; margin: auto;"></p>',
        );
    }

    public function testConvertImageAlt()
    {
        $this->assertConversionResult(
            '![Image <alt> "text"](http://local/image.jpg)',
            '<p><img src="http://local/image.jpg" alt="Image &lt;alt&gt; &quot;text&quot;" style="max-width: 100%;"></p>',
        );
    }

    public function testConvertImageDescription()
    {
        $this->assertConversionResult(
            '![](http://local/image.jpg "Image <description> "text"")',
            '<p><img src="http://local/image.jpg" alt="" title="Image &lt;description&gt; &quot;text&quot;" style="max-width: 100%;"></p>',
        );
    }

    public function testConvertImageAlignment()
    {
        $this->assertConversionResult(
            '![alt>](http://local/image.jpg "desc")',
            '<p><img class="pull-right" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; float: right;"></p>',
        );

        $this->assertConversionResult(
            '![alt<](http://local/image.jpg "desc")',
            '<p><img class="pull-left" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; float: left;"></p>',
        );

        $this->assertConversionResult(
            '![alt><](http://local/image.jpg "desc")',
            '<p><img class="center-block" src="http://local/image.jpg" alt="alt" title="desc" style="max-width: 100%; display: block; margin: auto;"></p>',
        );
    }

    public function testConvertImageSize()
    {
        $this->assertConversionResult(
            '![alt](http://local/image.jpg =100x)',
            '<p><img src="http://local/image.jpg" width="100" alt="alt" style="max-width: 100%;"></p>',
        );

        $this->assertConversionResult(
            '![alt](http://local/image.jpg =x200)',
            '<p><img src="http://local/image.jpg" height="200" alt="alt" style="max-width: 100%;"></p>',
        );

        $this->assertConversionResult(
            '![alt](http://local/image.jpg =50x120)',
            '<p><img src="http://local/image.jpg" width="50" height="120" alt="alt" style="max-width: 100%;"></p>',
        );
    }

    private function assertConversionResult($markdown, $expected = null, $options = [])
    {
        if ($expected === null) {
            $expected = $markdown;
        }

        $result = RichTextToEmailHtmlConverter::process($markdown, $options);

        $expected = trim(str_replace(["\n", "\r"], '', $expected));
        $result = trim(str_replace(["\n", "\r"], '', $result));

        static::assertEquals($expected, $result);
    }

    public function testFileTokenForReceiverAccess()
    {
        $receiverUser = User::findOne(['id' => 1]);
        $postedFile = new File();
        $postedFile->save();

        // Check markdown image tag is converted to html tag with JWT token in params
        $sourceMessage = '![test.txt](file-guid:' . $postedFile->guid . ' "test.txt")';
        $convertedMessage = RichTextToEmailHtmlConverter::process($sourceMessage, ['receiver' => $receiverUser]);
        $this->assertTrue($sourceMessage != $convertedMessage);

        // Grab token from the processed email message
        $tokenIsGenerated = preg_match('/<img src=".+&amp;token=(.+?)"/i', $convertedMessage, $parsedTokenData);
        $this->assertTrue((bool)$tokenIsGenerated);

        // Compare generated token with parsed from email message
        $parsedTokenFromEmailMessage = (isset($parsedTokenData[1]) ? $parsedTokenData[1] : null);
        $generatedToken = DownloadAction::generateDownloadToken($postedFile, $receiverUser);
        $this->assertEquals($parsedTokenFromEmailMessage, $generatedToken);

        // Make sure the User from token is same as receiver of the email message
        $tokenUser = DownloadAction::getUserByDownloadToken($parsedTokenFromEmailMessage, $postedFile);
        $this->assertEquals($tokenUser, $receiverUser);
    }

    public function testConvertEmojiToUnicode()
    {
        $this->assertConversionResult(
            ':grinning face: :winking face: :smiling face with tear: :shushing face: :unamused face: :face with thermometer: :cowboy hat face: :partying face: :disguised face: :smiling face with sunglasses:',
            '<p>😀 😉 🥲 🤫 😒 🤒 🤠 🥳 🥸 😎</p>',
        );

        $this->assertConversionResult(
            ':waving hand: :pinching hand: :backhand index pointing down: :heart hands: :leg: :eye: :person beard: :mouth: :brain: :man red hair:',
            '<p>👋 🤏 👇 🫶 🦵 👁️ 🧔 👄 🧠 👨‍🦰</p>',
        );

        $this->assertConversionResult(
            ':monkey face: :raccoon: :zebra: :ram: :mouse face: :polar bear: :rooster: :skunk: :rabbit face: :front-facing baby chick:',
            '<p>🐵 🦝 🦓 🐏 🐭 🐻‍❄️ 🐓 🦨 🐰 🐥</p>',
        );

        $this->assertConversionResult(
            ':grapes: :peach: :carrot: :chestnut: :poultry leg: :stuffed flatbread: :salt: :sushi: :amphora: :fork and knife:',
            '<p>🍇 🍑 🥕 🌰 🍗 🥙 🧂 🍣 🏺 🍴</p>',
        );

        $this->assertConversionResult(
            ':globe showing Europe-Africa: :camping: :hut: :love hotel: :mosque: :water wave: :closed umbrella: :sun behind small cloud: :crescent moon: :snowflake:',
            '<p>🌍 🏕️ 🛖 🏩 🕌 🌊 🌂 🌤️ 🌙 ❄️</p>',
        );

        $this->assertConversionResult(
            ':jack-o-lantern: :Japanese dolls: :trophy: :rugby football: :bullseye: :puzzle piece: :mahjong red dragon: :knot: :yarn: :video game:',
            '<p>🎃 🎎 🏆 🏉 🎯 🧩 🀄 🪢 🧶 🎮</p>',
        );

        $this->assertConversionResult(
            ':glasses: :socks: :clutch bag: :woman’s boot: :identification card: :sponge: :stethoscope: :magnet: :pill: :nazar amulet:',
            '<p>👓 🧦 👝 👢 🪪 🧽 🩺 🧲 💊 🧿</p>',
        );

        $this->assertConversionResult(
            ':ATM sign: :baggage claim: :no mobile phones: :up-left arrow: :ON! arrow: :star and crescent: :play button: :exclamation question mark: :registered: :black square button:',
            '<p>🏧 🛄 📵 ↖️ 🔛 ☪️ ▶️ ⁉️ ®️ 🔲</p>',
        );

        $this->assertConversionResult(
            ':flag United States: :flag Australia: :flag Botswana: :flag Germany: :flag Gabon: :flag Wales: :flag Ukraine: :flag South Sudan: :flag Sweden: :flag St. Martin:',
            '<p>🇺🇸 🇦🇺 🇧🇼 🇩🇪 🇬🇦 🏴󠁧󠁢󠁷󠁬󠁳󠁿 🇺🇦 🇸🇸 🇸🇪 🇲🇫</p>',
        );
    }

    public function testConvertAllEmojis()
    {
        $allEmojis = EmojiMap::getData();
        foreach ($allEmojis as $emoji => $unicode) {
            $this->assertConversionResult(
                addcslashes($emoji, '*') . ' = :' . $emoji . ':',
                '<p>' . str_replace('&', '&amp;', $emoji) . ' = ' . $unicode . '</p>',
            );
        }
    }
}

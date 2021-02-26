<?php


namespace humhub\modules\content\tests\codeception\unit;


use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use tests\codeception\_support\HumHubDbTestCase;

class RichtextExtensionTest extends HumHubDbTestCase
{
    public function testScanMultipleFileGuid()
    {
        $text = '[img3.jpg](http://humhub.com "img)3.jpg" xasdfjpös0as)

[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681)

test
![img3.jpg](file-guid:3f1e14a2-4375-434a-a554-a19ec5e48909 "img))))3.jpg")asdfas
asdfasdfasdf

[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681 "test")

asdfasdfasdf

[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681)';

        $matches = ProsemirrorRichText::scanLinkExtension($text, 'file-guid');

        static::assertCount(4, $matches);

        static::assertEquals('[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681)', $matches[0][0]);
        static::assertEquals('test2.txt', $matches[0][1]);
        static::assertEquals('file-guid', $matches[0][2]);
        static::assertEquals('aef0eb95-b715-4707-9792-180e4395e681', $matches[0][3]);

        static::assertEquals('![img3.jpg](file-guid:3f1e14a2-4375-434a-a554-a19ec5e48909 "img))))3.jpg")', $matches[1][0]);
        static::assertEquals('img3.jpg', $matches[1][1]);
        static::assertEquals('file-guid', $matches[1][2]);
        static::assertEquals('3f1e14a2-4375-434a-a554-a19ec5e48909', $matches[1][3]);
        static::assertEquals('img))))3.jpg', $matches[1][4]);

        static::assertEquals('[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681 "test")', $matches[2][0]);
        static::assertEquals('test2.txt', $matches[2][1]);
        static::assertEquals('file-guid', $matches[2][2]);
        static::assertEquals('aef0eb95-b715-4707-9792-180e4395e681', $matches[2][3]);
        static::assertEquals('test', $matches[2][4]);

        static::assertEquals('[test2.txt](file-guid:aef0eb95-b715-4707-9792-180e4395e681)', $matches[3][0]);
        static::assertEquals('test2.txt', $matches[3][1]);
        static::assertEquals('file-guid', $matches[3][2]);
        static::assertEquals('aef0eb95-b715-4707-9792-180e4395e681', $matches[3][3]);
    }
}

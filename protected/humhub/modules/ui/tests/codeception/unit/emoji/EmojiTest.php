<?php

namespace tests\codeception\unit;

use humhub\libs\EmojiMap;
use tests\codeception\_support\HumHubDbTestCase;

class EmojiTest extends HumHubDbTestCase
{
    /**
     * Make sure users with create topic permission sees topic picker
     */
    public function testEmojiMapCoversAllRichtextEmojis()
    {
        $emoji = json_decode(file_get_contents(__dir__ . DIRECTORY_SEPARATOR .'emoji.json'), true);
        foreach ($emoji as $key => $def) {
            $this->assertArrayHasKey($key, EmojiMap::MAP);
        }
    }
}

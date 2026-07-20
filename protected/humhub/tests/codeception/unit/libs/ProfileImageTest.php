<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\libs;

use humhub\libs\ProfileImage;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ProfileImageTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    public function testGetUrlForGuidConstructedInstance()
    {
        $url = (new ProfileImage(User::findOne(['username' => 'User1'])->guid))->getUrl();

        $this->assertNotEmpty($url);
    }

    public function testGetUrlForUnknownGuid()
    {
        $this->assertSame('', (new ProfileImage('00000000-0000-0000-0000-000000000000'))->getUrl());
    }
}

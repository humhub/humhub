<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\models;


use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\user\models\User;

class UsernameTest extends HumHubDbTestCase
{

    public function testUserNameValidation()
    {
        $user = User::findOne(['id' => 1]);
        $user->username = 'valid';
        $this->assertTrue($user->validate('username'));
        $user->username = "test\x00Char";
        $this->assertFalse($user->validate('username'));
        $user->username = 'test/slash';
        $this->assertFalse($user->validate('username'));
        $user->username = '123890AßäöüÄÖÜĆ_-@#$%^&*()[]{}+=<>:;,.?!|~"\'\\';
        $this->assertFalse($user->validate('username'));
        $user->username = 'user@example.com';
        $this->assertTrue($user->validate('username'));
        $user->username = 'user-name';
        $this->assertTrue($user->validate('username'));
        $user->username = 'user_name';
        $this->assertTrue($user->validate('username'));
        $user->username = 'user.name';
        $this->assertTrue($user->validate('username'));
    }

}

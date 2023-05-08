<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\UserPicker;
use tests\codeception\_support\HumHubDbTestCase;

class UserPickerTest extends HumHubDbTestCase
{
    public function testReturnFilteredUsers()
    {
        $users = UserPicker::filter(['maxResult' => 3, 'keyword' => 'Admin']);
        $this->assertEquals(2, count($users));
        $this->assertEquals('Admin Tester', $users[0]['text']);

        $users = UserPicker::filter(['maxResult' => 3, 'keyword' => 'Admin', 'fillUser' => true]);
        $this->assertEquals(2, count($users));
    }

}

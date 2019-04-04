<?php

namespace tests\codeception\unit;

use humhub\modules\user\widgets\UserPickerField;
use tests\codeception\_support\HumHubDbTestCase;

class UserPickerFieldTest extends HumHubDbTestCase
{
    public function testItemKey()
    {
        $picker = new UserPickerField();
        $this->assertEquals('guid', $picker->itemKey);

        $picker = new UserPickerField(['itemKey' => 'id']);
        $this->assertEquals('id', $picker->itemKey);
    }

    public function testDefaultRoute()
    {
        $picker = new UserPickerField();
        $this->assertEquals('/user/search/json', $picker->defaultRoute);
    }
}

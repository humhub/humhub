<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\modules\notification\components\NotificationAction;

class NotificationActionTest extends Unit
{
    public function testHoldsLabelAndUrl()
    {
        $action = new NotificationAction('Open task', 'https://example.com/task/1');
        $this->assertSame('Open task', $action->label);
        $this->assertSame('https://example.com/task/1', $action->url);
        $this->assertEquals(new NotificationAction(url: 'https://example.com/task/1', label: 'Open task'), $action);
    }

    public function testIsImmutable()
    {
        $action = new NotificationAction('Open', 'https://example.com');
        $this->expectException(\Error::class);
        $action->label = 'Changed';
    }
}

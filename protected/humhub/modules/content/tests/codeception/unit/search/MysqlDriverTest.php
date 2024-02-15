<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\MysqlDriver;

class MysqlDriverTest extends AbstractDriverTestSuite
{
    protected function createDriver(): AbstractDriver
    {
        return new MysqlDriver();
    }

    protected function updateNewAddedContents(): void
    {
    }
}

<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\ZendLucenceDriver;

class ZendLucenceDriverTest extends AbstractDriverTestSuite
{
    protected function createDriver(): AbstractDriver
    {
        return new ZendLucenceDriver();
    }

    /**
     * @skip This driver cannot find URL
     */
    public function testUrlKeywords()
    {
    }
}

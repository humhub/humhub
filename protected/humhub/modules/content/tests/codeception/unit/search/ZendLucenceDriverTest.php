<?php

namespace humhub\modules\content\tests\codeception\unit\search;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\ZendLucenceDriver;

class ZendLucenceDriverTest extends AbstractDriverTestSuite
{
    protected function createDriver(): AbstractDriver
    {
        return new ZendLucenceDriver();
    }

    protected function updateNewAddedContents(): void
    {
        foreach (Content::find()->where(['visibility' => Content::VISIBILITY_PUBLIC])->each() as $content) {
            $this->searchDriver->delete($content);
            $this->searchDriver->update($content);
        }
    }
}

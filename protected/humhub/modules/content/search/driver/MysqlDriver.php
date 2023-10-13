<?php

namespace humhub\modules\content\search\driver;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;

class MysqlDriver extends AbstractDriver
{

    public function purge()
    {
        // TODO: Implement purge() method.
    }

    public function update(Content $content)
    {
        // TODO: Implement update() method.
    }

    public function delete(Content $content)
    {
        // TODO: Implement delete() method.
    }

    public function search(SearchRequest $request): ResultSet
    {
        // TODO: Implement search() method.
    }
}

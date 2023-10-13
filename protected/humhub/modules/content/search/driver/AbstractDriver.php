<?php

namespace humhub\modules\content\search\driver;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;

abstract class AbstractDriver
{

    abstract public function purge(): void;

    abstract public function update(Content $content): void;

    abstract public function delete(Content $content): void;

    /**
     * Search
     *
     * // Add private content, which is in Space content containers where the user is member of
     * // Add private content, of User content containers where the user is friend or self
     *
     * // Add all public content
     * @param $query
     * @param SearchRequest $request
     * @return mixed
     */
    abstract public function search(SearchRequest $request): ResultSet;
}

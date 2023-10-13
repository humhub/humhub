<?php

namespace humhub\modules\content\interfaces;

interface Searchable extends \humhub\modules\search\interfaces\Searchable
{
    public function getSearchAttributes();
}

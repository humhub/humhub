<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\modules\content\models\Content;
use yii\data\Pagination;
use yii\db\Expression;

/**
 * SearchResultSet
 *
 * @author luke
 */
class ResultSet
{
    /**
     * @var Content[]
     */
    public $results = [];

    /**
     * @var Pagination
     */
    public $pagination;

    public function __serialize(): array
    {
        return [
            'results' => array_map(function (Content $result) {return $result->id;}, $this->results),
            'pagination' => $this->pagination,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->pagination = $data['pagination'];
        $this->results = empty($data['results'])
            ? []
            : Content::find()
                ->where(['IN', 'id', $data['results']])
                // Restore an original order what was before serializing
                ->orderBy([new Expression('FIELD(id, ' . implode(',', $data['results']) . ')')])
                ->all();
    }
}

<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;


use yii\db\Expression;
use yii\db\Query;

class TopicStreamFilter extends StreamQueryFilter
{
    const CATEGORY = 'topics';

    /**
     * Array of active topic filters.
     *
     * @var array
     */
    public $topics = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topics'], 'safe']
        ];
    }

    public function apply()
    {
        if(empty($this->topics)) {
            return;
        }

        $subQuery = (new Query)->select(['count(*)'])
            ->from('content_tag_relation')
            ->where(['and', 'content_tag_relation.content_id = content.id', ['in', 'content_tag_relation.tag_id', $this->topics]]);

        $this->query->andWhere( ['=', new Expression('('.count($this->topics).')'), $subQuery]);
    }
}

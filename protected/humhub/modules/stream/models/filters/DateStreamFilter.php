<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

class DateStreamFilter extends StreamQueryFilter
{
    const CATEGORY_FROM = 'date_filter_from';
    const CATEGORY_TO = 'date_filter_to';

    /**
     * Created from date
     * @var string
     */
    public $date_filter_from;

    /**
     * Created to date
     * @var string
     */
    public $date_filter_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_filter_from', 'date_filter_to'], 'safe']
        ];
    }

    public function apply()
    {
        if (!empty($this->date_filter_from)) {
            $this->query->andWhere([
                '>=',
                'content.created_at',
                date('Y-m-d 00:00:00', strtotime($this->date_filter_from))
            ]);
        }

        if (!empty($this->date_filter_to)) {
            $this->query->andWhere([
                '<=',
                'content.created_at',
                date('Y-m-d 23:59:59', strtotime($this->date_filter_to))
            ]);
        }
    }
}

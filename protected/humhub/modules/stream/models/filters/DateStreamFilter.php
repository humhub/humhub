<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use DateTime;
use DateTimeZone;
use Yii;
use yii\helpers\FormatConverter;

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
                $this->formatDateToMysql($this->date_filter_from) . ' 00:00:00'
            ]);
        }

        if (!empty($this->date_filter_to)) {
            $this->query->andWhere([
                '<=',
                'content.created_at',
                $this->formatDateToMysql($this->date_filter_to) . ' 23:59:59'
            ]);
        }
    }

    private function formatDateToMysql(string $date): string
    {
        $localeDateFormat = FormatConverter::convertDateIcuToPhp(Yii::$app->formatter->dateInputFormat);
        $timeZone = new DateTimeZone(Yii::$app->formatter->timeZone);

        return DateTime::createFromFormat($localeDateFormat, $date, $timeZone)
            ->format('Y-m-d');
    }
}

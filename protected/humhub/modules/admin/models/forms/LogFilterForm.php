<?php


namespace humhub\modules\admin\models\forms;


use DateTime;
use humhub\libs\DateHelper;
use humhub\modules\admin\models\Log;
use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

/**
 * Model used for administration log filtering.
 */
class LogFilterForm extends Model
{
    /**
     * default pagination  size
     */
    const PAGE_SIZE = 10;


    /**
     * default category filter for including all
     */
    const FILTER_CATEGORY_NONE = 'none';

    /**
     * special category filter for merging yii/* filter categories
     */
    const FILTER_CATEGORY_OTHER = 'other';

    /**
     * @var string single category filter
     */
    public $category;

    /**
     * @var string search term used to filter log category and message
     */
    public $term;

    /**
     * @var string date string used to filter out log entries outside of the given date
     */
    public $day;

    /**
     * @var array log levels to include, if empty all levels are included
     */
    public $levels;

    /**
     * @var Pagination
     */
    private $pagination;

    /**
     * @var ActiveQuery
     */
    private $query;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if($this->levels === null) {
            $this->levels = [Logger::LEVEL_ERROR];
        }

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'term'], 'string'],
            [['day'], 'date'],
            [['levels'], 'integer'],
        ];
    }

    /**
     * @return Log[]
     */
    public function findEntries()
    {
        $this->query = Log::find();
        $this->query->orderBy('id DESC');

        $this->filterTerm();
        $this->filterLevels();
        $this->filterCategory();
        $this->filterDay();

        $countQuery = clone $this->query;

        $test = $this->getUrlParams();

        $this->pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => static::PAGE_SIZE,
            'params' => $this->getUrlParams()
        ]);

        $this->query->offset($this->pagination->offset)->limit($this->pagination->limit);

        return $this->query->all();
    }

    /**
     * @return array pagination url params used to build pagination links
     */
    private function getUrlParams()
    {
        $result = ArrayHelper::merge(Yii::$app->request->get(), [
            'category' => $this->category,
            'day' => $this->day,
            'term' => $this->term
        ]);

        $result['levels'] = $this->levels;
        return $result;
    }

    /**
     * @return string the current page url with filters
     */
    public function getUrl() {
        return $this->pagination->createUrl($this->pagination->getPage(true), static::PAGE_SIZE);
    }

    /**
     * Filter function for terms filter. This filter will search for the term within message and category field.
     */
    private function filterTerm()
    {
        if(empty($this->term)) {
            return;
        }

        $this->query->andWhere(
            ['or',
                ['LIKE', 'message', $this->term],
                ['LIKE', 'category', $this->term],
            ]
        );
    }

    /**
     * Log level filter function. This filter will filter out all log entries with a log level not included in $levels
     * in case $level array is not null.
     */
    private function filterLevels()
    {
        if(empty($this->levels)) {
            return;
        }

        $this->query->andWhere(['IN', 'level', $this->levels]);
    }

    /**
     * Log category filter. This filter will only include filters of the given log level. There are two types of special values:
     *
     *  - FILTER_CATEGORY_NONE: Will include all categories
     *  - FILTER_CATEGORY_OTHER: Will include yii/* categories
     */
    private function filterCategory()
    {
        if(empty($this->category) || $this->category === static::FILTER_CATEGORY_NONE) {
            return;
        }

        if($this->category === static::FILTER_CATEGORY_OTHER) {
            $this->query->andWhere(['LIKE', 'category', 'yii\\']);
            return;
        }

        $this->query->andWhere(['category' => $this->category]);
    }

    /**
     * Log time filter. This filter only includes log entries with a log_time within the given day.
     */
    private function filterDay()
    {
        try {
            if(empty($this->day)) {
                return;
            }

            $dayDT = new DateTime(DateHelper::parseDateTime($this->day), DateHelper::getSystemTimeZone());

            $endDT = clone $dayDT;
            $endDT->setTime(23,59,59);
            $end = $endDT->getTimestamp();

            $start = $dayDT->setTime(0,0,0)->getTimestamp();

            $this->query->andWhere(['<=', 'log_time', $end]);
            $this->query->andWhere(['>=', 'log_time', $start]);

        } catch (\Exception $e) {
            Yii::error($e, 'admin');
        }
    }

    /**
     * @return Pagination
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * @return array
     */
    public function getLevelSelection()
    {
        $levelsArr = Log::find()->select('level')->distinct()->asArray()->all();

        $result = [];
        foreach ($levelsArr as $logArr) {
            if(!isset($logArr['level']) || !static::getLevelLabel($logArr['level'])) {
                continue;
            }

            $level = $logArr['level'];
            $result[$level] = static::getLevelLabel($level);
        }

        if (is_array($this->levels)) {
            foreach ($this->levels as $defaultLevel) {
                if (!isset($result[$defaultLevel]) && ($defaultLevelTitle = static::getLevelLabel($defaultLevel))) {
                    $result[$defaultLevel] = $defaultLevelTitle;
                }
            }
        }

        return $result;
    }

    /**
     * @param String translated label for a given log level
     * @return string|null
     */
    public static function getLevelLabel($level)
    {
        switch ($level) {
            case Logger::LEVEL_INFO:
                return Yii::t('AdminModule.information', 'Info');
            case Logger::LEVEL_WARNING:
                return Yii::t('AdminModule.information', 'Warning');
            case Logger::LEVEL_ERROR:
                return Yii::t('AdminModule.information', 'Error');
            case Logger::LEVEL_TRACE:
                return Yii::t('AdminModule.information', 'Trace');
        }
        return null;
    }


    /**
     * @return array
     */
    public function getCategorySelection()
    {
        $categoryArr = Log::find()->select('category')->distinct()->asArray()->all();

        $result = [static::FILTER_CATEGORY_NONE => Yii::t('AdminModule.information','Select category..')];
        foreach ($categoryArr as $logArr) {
            if(!isset($logArr['category']) || strpos($logArr['category'], 'yii\\') === 0) {
                continue;
            }

            $category = $logArr['category'];
            $result[$category] = $category;
        }

        $result[static::FILTER_CATEGORY_OTHER] = Yii::t('AdminModule.information', 'Other');

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return '';
    }
}

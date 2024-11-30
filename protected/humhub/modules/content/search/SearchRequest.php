<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use DateTime;
use humhub\libs\SearchQuery;
use humhub\modules\content\models\ContentType;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\FormatConverter;

class SearchRequest extends Model
{
    public const ORDER_BY_CREATION_DATE = 'content.created_at';
    public const ORDER_BY_SCORE = 'score';
    public const DATE_FORMAT = 'short';

    public ?User $user = null;

    public string $keyword = '';

    public $page = 1;

    public int $pageSize = 25;

    public $contentType = '';

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public array $topic = [];

    public array $author = [];

    public ?string $contentContainerClass = null;

    public array $contentContainer = [];

    public $orderBy = self::ORDER_BY_CREATION_DATE;

    public ?SearchQuery $searchQuery = null;

    public function init()
    {
        if ($this->user === null) {
            $this->user = Yii::$app->user->getIdentity();
        }

        parent::init();
    }

    public function rules()
    {
        return [
            [['keyword', 'topic', 'author', 'contentContainerClass', 'contentContainer'], 'safe'],
            [['keyword'], 'required'],
            [['contentType'], 'in', 'range' => array_keys(static::getContentTypes())],
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:' . FormatConverter::convertDateIcuToPhp(self::DATE_FORMAT)],
            [['page'], 'integer'],
            //[['pageSize'], 'numeric'],
            [['orderBy'], 'in', 'range' => [static::ORDER_BY_SCORE, static::ORDER_BY_CREATION_DATE]],
        ];
    }

    public function getKeywords(): array
    {
        return explode(' ', $this->keyword);
    }

    public static function getContentTypes(): array
    {
        $result = [];
        foreach (ContentType::getContentTypes() as $contentType) {
            $result[$contentType->typeClass] = ucfirst($contentType->getContentName());
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        parent::afterValidate();

        $this->normalizeDate('dateFrom');
        $this->normalizeDate('dateTo');
    }

    protected function normalizeDate(string $dateFieldName)
    {
        if ($this->hasErrors($dateFieldName) || empty($this->$dateFieldName)) {
            return;
        }

        $format = FormatConverter::convertDateIcuToPhp(self::DATE_FORMAT, 'date', Yii::$app->formatter->locale);

        $this->$dateFieldName = DateTime::createFromFormat($format, $this->$dateFieldName)->format('Y-m-d');
    }

    public function getSearchQuery(): SearchQuery
    {
        if ($this->searchQuery === null) {
            $this->searchQuery = new SearchQuery($this->keyword);
        }

        return $this->searchQuery;
    }

}

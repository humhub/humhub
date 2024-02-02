<?php

namespace humhub\modules\content\search;

use humhub\modules\content\models\ContentType;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\web\IdentityInterface;

class SearchRequest extends Model
{
    public const ORDER_BY_CREATION_DATE = 'content.created_at';
    public const ORDER_BY_SCORE = 'score';

    public ?User $user = null;

    public string $keyword = '';

    public $page = 1;

    public $pageSize = 25;

    public $contentType = '';

    public $contentContainer = [];

    public $orderBy = 'content.created_at';

    public ?User $author = null;

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
            [['keyword'], 'safe'],
            [['keyword'], 'required'],
            [['contentType'], 'in', 'range' => array_keys(static::getContentTypes())],
            //[['page'], 'numeric'],
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

}

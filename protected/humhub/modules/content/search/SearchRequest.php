<?php

namespace humhub\modules\content\search;

use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\web\IdentityInterface;

class SearchRequest extends Model
{
    public ?User $user = null;

    public string $keyword = '';

    public $page = 1;

    public $pageSize = 25;

    public $contentTypes = [];

    public $contentContainer = [];

    public $orderBy = 'content.created_at';



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
            //[['page'], 'numeric'],
            //[['pageSize'], 'numeric'],
            //[['contentTypes'], 'in', static::getContentTypes()],
            //[['orderBy'], 'in', []],
        ];
    }

    public static function getContentTypes(): array
    {

    }

}
